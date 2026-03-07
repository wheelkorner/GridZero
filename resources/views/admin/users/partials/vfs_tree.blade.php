@php
    $node = $vfs[(string) $path] ?? null;
    $indent = $indent ?? 0;
@endphp

@if($node && isset($node['type']))
    <div style="margin-left: {{ $indent * 20 }}px; font-family: 'Courier New', Courier, monospace; color: #39ff14;">
        @if($node['type'] === 'dir')
            <div class="vfs-dir">
                <i class="fas fa-folder text-warning"></i>
                <strong>{{ $path === '/' ? '/' : basename($path) }}</strong>
            </div>
            @if(isset($node['children']))
                @foreach($node['children'] as $child)
                    @php
                        $childPath = $path === '/' ? "/{$child}" : "{$path}/{$child}";
                    @endphp
                    @include('admin.users.partials.vfs_tree', ['path' => (string) $childPath, 'vfs' => $vfs, 'indent' => $indent + 1])
                @endforeach
            @endif
        @else
            <div class="vfs-file">
                <i class="fas fa-file-alt text-info"></i>
                <span>{{ basename($path) }}</span>
                @if(isset($node['content']))
                    <small class="text-muted ml-2" title="{{ $node['content'] }}">(content detected)</small>
                @endif
            </div>
        @endif
    </div>
@endif