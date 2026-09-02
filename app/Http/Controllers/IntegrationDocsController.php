<?php

namespace App\Http\Controllers;

use App\Support\IntegrationDocs;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IntegrationDocsController extends Controller
{
    public function __construct(
        private IntegrationDocs $docs,
    ) {}

    public function index(): View
    {
        $document = $this->docs->resolve('README');
        abort_unless($document, 404);

        return view('pages.integration-docs', [
            'document' => $document,
            'navigation' => $this->docs->navigation(),
        ]);
    }

    public function show(?string $path = null): View
    {
        $document = $this->docs->resolve($path ?? 'README');
        abort_unless($document, 404);

        return view('pages.integration-docs', [
            'document' => $document,
            'navigation' => $this->docs->navigation(),
        ]);
    }

    public function download(string $path): BinaryFileResponse
    {
        $response = $this->docs->rawDownload($path);
        abort_unless($response instanceof BinaryFileResponse, 404);

        return $response;
    }
}
