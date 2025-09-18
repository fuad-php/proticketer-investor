<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\User;
use App\Models\Investor;
use App\Models\Client;
use App\Models\Order;
use App\Models\Investment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class DocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view documents')->only(['index', 'show', 'preview']);
        $this->middleware('permission:create documents')->only(['create', 'store']);
        $this->middleware('permission:edit documents')->only(['edit', 'update']);
        $this->middleware('permission:delete documents')->only(['destroy']);
        $this->middleware('permission:approve documents')->only(['approve']);
    }

    public function index(Request $request)
    {
        $query = Document::with(['uploadedBy', 'approvedBy', 'reference']);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by visibility
        if ($request->filled('visibility')) {
            $query->where('visibility', $request->visibility);
        }

        // Filter by reference
        if ($request->filled('reference_type') && $request->filled('reference_id')) {
            $query->where('reference_type', $request->reference_type)
                  ->where('reference_id', $request->reference_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('document_number', 'like', "%{$search}%");
            });
        }

        // Filter by tags
        if ($request->filled('tags')) {
            $tags = explode(',', $request->tags);
            foreach ($tags as $tag) {
                $query->whereJsonContains('tags', trim($tag));
            }
        }

        $documents = $query->latest()->paginate(20);
        $stats = $this->getDocumentStats();

        return view('documents.index', compact('documents', 'stats'));
    }

    public function create()
    {
        $referenceTypes = [
            'order' => 'Order',
            'investment' => 'Investment',
            'inquiry' => 'Inquiry',
            'client' => 'Client',
            'investor' => 'Investor',
        ];

        return view('documents.create', compact('referenceTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:statement,receipt,contract,invoice,report,other',
            'category' => 'required|string|in:investment,client,system,legal,financial',
            'file' => 'required|file|max:10240', // 10MB max
            'visibility' => 'required|string|in:public,private,restricted',
            'tags' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'expires_at' => 'nullable|date|after:today',
        ]);

        try {
            $file = $request->file('file');
            $storagePath = Document::getStoragePath($request->type, $request->category);
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($storagePath, $fileName, 'public');

            $tags = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

            $document = Document::createFromFile(
                $filePath,
                $request->title,
                $request->type,
                $request->category,
                Auth::user(),
                [
                    'description' => $request->description,
                    'visibility' => $request->visibility,
                    'tags' => $tags,
                    'reference_type' => $request->reference_type,
                    'reference_id' => $request->reference_id,
                    'expires_at' => $request->expires_at,
                ]
            );

            return redirect()->route('documents.show', $document)
                ->with('success', 'Document uploaded successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to upload document: ' . $e->getMessage());
        }
    }

    public function show(Document $document)
    {
        if (!$document->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to view this document.');
        }

        $document->load(['uploadedBy', 'approvedBy', 'reference']);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this document.');
        }

        $referenceTypes = [
            'order' => 'Order',
            'investment' => 'Investment',
            'inquiry' => 'Inquiry',
            'client' => 'Client',
            'investor' => 'Investor',
        ];

        return view('documents.edit', compact('document', 'referenceTypes'));
    }

    public function update(Request $request, Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to edit this document.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:statement,receipt,contract,invoice,report,other',
            'category' => 'required|string|in:investment,client,system,legal,financial',
            'visibility' => 'required|string|in:public,private,restricted',
            'tags' => 'nullable|string',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|integer',
            'expires_at' => 'nullable|date|after:today',
        ]);

        $tags = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

        $document->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'category' => $request->category,
            'visibility' => $request->visibility,
            'tags' => $tags,
            'reference_type' => $request->reference_type,
            'reference_id' => $request->reference_id,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('documents.show', $document)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        if (!$document->canBeDeletedBy(Auth::user())) {
            abort(403, 'You do not have permission to delete this document.');
        }

        try {
            // Soft delete the document
            $document->softDelete();
            
            // Optionally delete the file from storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            return redirect()->route('documents.index')
                ->with('success', 'Document deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }

    public function download(Document $document)
    {
        if (!$document->canBeDownloadedBy(Auth::user())) {
            abort(403, 'You do not have permission to download this document.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $document->incrementDownloadCount();

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function preview(Document $document)
    {
        if (!$document->canBeViewedBy(Auth::user())) {
            abort(403, 'You do not have permission to view this document.');
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found.');
        }

        $document->incrementDownloadCount();

        $filePath = Storage::disk('public')->path($document->file_path);
        $mimeType = $document->mime_type;

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $document->file_name . '"',
        ]);
    }

    public function approve(Document $document)
    {
        if (!Auth::user()->hasRole(['super_admin', 'director', 'managing_director'])) {
            abort(403, 'You do not have permission to approve documents.');
        }

        $document->approve(Auth::user());

        return back()->with('success', 'Document approved successfully.');
    }

    public function archive(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to archive this document.');
        }

        $document->archive();

        return back()->with('success', 'Document archived successfully.');
    }

    public function restore(Document $document)
    {
        if (!$document->canBeEditedBy(Auth::user())) {
            abort(403, 'You do not have permission to restore this document.');
        }

        $document->restore();

        return back()->with('success', 'Document restored successfully.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string|in:archive,restore,delete,approve',
            'document_ids' => 'required|array',
            'document_ids.*' => 'exists:documents,id',
        ]);

        $documents = Document::whereIn('id', $request->document_ids)->get();
        $processed = 0;

        foreach ($documents as $document) {
            try {
                switch ($request->action) {
                    case 'archive':
                        if ($document->canBeEditedBy(Auth::user())) {
                            $document->archive();
                            $processed++;
                        }
                        break;
                    case 'restore':
                        if ($document->canBeEditedBy(Auth::user())) {
                            $document->restore();
                            $processed++;
                        }
                        break;
                    case 'delete':
                        if ($document->canBeDeletedBy(Auth::user())) {
                            $document->softDelete();
                            $processed++;
                        }
                        break;
                    case 'approve':
                        if (Auth::user()->hasRole(['super_admin', 'director', 'managing_director'])) {
                            $document->approve(Auth::user());
                            $processed++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                // Log error but continue processing
                \Log::error('Bulk action failed for document ' . $document->id . ': ' . $e->getMessage());
            }
        }

        return back()->with('success', "Processed {$processed} documents successfully.");
    }

    public function stats()
    {
        $stats = Document::getStorageStats();
        return view('documents.stats', compact('stats'));
    }

    private function getDocumentStats()
    {
        return [
            'total_documents' => Document::where('status', 'active')->count(),
            'total_size' => Document::where('status', 'active')->sum('file_size'),
            'by_type' => Document::where('status', 'active')
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get()
                ->keyBy('type'),
            'by_category' => Document::where('status', 'active')
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get()
                ->keyBy('category'),
            'pending_approval' => Document::where('status', 'active')
                ->whereNull('approved_at')
                ->count(),
            'expired' => Document::where('status', 'active')
                ->where('expires_at', '<', now())
                ->count(),
        ];
    }
}
