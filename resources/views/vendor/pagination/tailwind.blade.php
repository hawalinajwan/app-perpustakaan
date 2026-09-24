{{-- Override view pagination bawaan Laravel (default: Tailwind) agar tampil bersih tanpa CSS Tailwind. --}}
{{-- File: resources/views/vendor/pagination/tailwind.blade.php --}}
@if ($paginator->hasPages())
    <style>
        .app-pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-top: 16px; }
        .app-pagination .info { color: #6b7280; font-size: 14px; margin: 0; }
        .app-pagination ul { list-style: none; display: flex; gap: 6px; margin: 0; padding: 0; }
        .app-pagination a, .app-pagination span { display: inline-block; padding: 6px 12px; border: 1px solid #d1d5db; border-radius: 4px; text-decoration: none; color: #1f2937; font-size: 14px; }
        .app-pagination a:hover { background: #eff6ff; border-color: #2563eb; color: #2563eb; }
        .app-pagination .active span { background: #2563eb; border-color: #2563eb; color: #fff; }
        .app-pagination .disabled span { color: #9ca3af; background: #f9fafb; }
    </style>

    <nav class="app-pagination" role="navigation" aria-label="Navigasi halaman">
        <p class="info">Menampilkan {{ $paginator->firstItem() ?? 0 }}&ndash;{{ $paginator->lastItem() ?? 0 }} dari {{ $paginator->total() }} data</p>

        <ul>
            @if ($paginator->onFirstPage())
                <li class="disabled"><span>&laquo; Sebelumnya</span></li>
            @else
                <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Sebelumnya</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled"><span>{{ $element }}</span></li>
                @else
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya &raquo;</a></li>
            @else
                <li class="disabled"><span>Berikutnya &raquo;</span></li>
            @endif
        </ul>
    </nav>
@endif
