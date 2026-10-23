<div class="list-pagination">
    <p class="list-pagination-summary">
        Menampilkan <strong>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}</strong>
        dari <strong>{{ number_format($paginator->total(), 0, ',', '.') }}</strong> data
    </p>
    @if ($paginator->hasPages())
        <nav aria-label="Navigasi halaman daftar">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    @if ($paginator->onFirstPage())
                        <span class="page-link" aria-disabled="true" aria-label="Halaman pertama">&laquo;</span>
                    @else
                        <a class="page-link" href="{{ $paginator->url(1) }}" aria-label="Halaman pertama" title="Halaman pertama">&laquo;</a>
                    @endif
                </li>
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    @if ($paginator->onFirstPage())
                        <span class="page-link" aria-disabled="true" aria-label="Halaman sebelumnya">&lsaquo;</span>
                    @else
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya" title="Halaman sebelumnya">&lsaquo;</a>
                    @endif
                </li>
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled paging-number"><span class="page-link">{{ $element }}</span></li>
                    @else
                        @foreach ($element as $page => $url)
                            <li class="page-item paging-number {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                                @if ($page === $paginator->currentPage())
                                    <span class="page-link" aria-current="page" aria-label="Halaman {{ $page }}">{{ $page }}</span>
                                @else
                                    <a class="page-link" href="{{ $url }}" aria-label="Halaman {{ $page }}">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach
                <li class="page-item paging-mobile"><span class="page-link" aria-current="page">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span></li>
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    @if ($paginator->hasMorePages())
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya" title="Halaman berikutnya">&rsaquo;</a>
                    @else
                        <span class="page-link" aria-disabled="true" aria-label="Halaman berikutnya">&rsaquo;</span>
                    @endif
                </li>
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    @if ($paginator->hasMorePages())
                        <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}" aria-label="Halaman terakhir" title="Halaman terakhir">&raquo;</a>
                    @else
                        <span class="page-link" aria-disabled="true" aria-label="Halaman terakhir">&raquo;</span>
                    @endif
                </li>
            </ul>
        </nav>
    @endif
</div>
