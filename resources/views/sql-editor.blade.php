@extends('layouts.app')

@section('page_title', $currentConnection->name . ': ' . $currentDatabase . (isset($currentCollection) ? '.'. $currentCollection : ''))

@include('partials.connection-databases')

@push('nav-right')
    <ul class="navbar-nav px-3">
        <li class="nav-item dropdown text-end">
            <a class="nav-link dropdown-toggle" href="#" id="dropdown01" data-bs-toggle="dropdown">Style: <span
                    class="current-style"></span></a>
            <ul class="dropdown-menu dropdown-menu-end position-absolute dropdown-style" aria-labelledby="dropdown01">
                @foreach(config('highlight.styles') as $key => $label)
                    <li class="highlight-style-item">
                        <a data-key="{{ $key }}" class="dropdown-item" href="javascript:;">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </li>
    </ul>
@endpush

@push('content')
    <p class="mb-2">Run SQL query on {{ isset($currentCollection) ? 'collection' : 'database' }}: <strong
            style="color: {{ $currentConnection->color }}">{{ $currentDatabase }}{{ isset($currentCollection) ? ".{$currentCollection}" : '' }}</strong>
    </p>
    <textarea id="code" name="code">{{ $sql }}</textarea>
    <div class="mt-2">
        <button id="btn-run" type="button" class="btn btn-sm btn-success">Run query</button>
        <small style="font-style: italic; color: #7b7b7b">or Press "Ctrl/Cmd + Enter"</small>
    </div>
    <hr>
    <div class="mt-2">
        <livewire:sql-result :sql="$sql" :connectionId="$currentConnection->id" :database="$currentDatabase"/>
    </div>
@endpush

@push('styles')
    <link rel="icon" href="{{ route('connections.favicon', $currentConnection) }}?v={{ microtime(true) }}"
          type="image/png" sizes="20x20">
    <link rel="stylesheet" href="{{ asset('codemirror/lib/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('codemirror/addon/hint/show-hint.css') }}">
    <link id="highlight-style-link" rel="stylesheet" href="{{ asset('highlightjs/styles/default.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('codemirror/lib/codemirror.js') }}"></script>
    <script src="{{ asset('codemirror/addon/edit/matchbrackets.js') }}"></script>
    <script src="{{ asset('codemirror/mode/sql/sql.js') }}"></script>
    <script src="{{ asset('codemirror/addon/hint/show-hint.js') }}"></script>
    <script src="{{ asset('codemirror/addon/hint/sql-hint.js') }}"></script>
    <script src="{{ asset('highlightjs/highlight.pack.js') }}"></script>
    <script>
        (() => {
            let editor = CodeMirror.fromTextArea(document.getElementById('code'), {
                mode: 'text/x-mariadb',
                indentWithTabs: true,
                smartIndent: true,
                lineNumbers: true,
                matchBrackets: true,
                autofocus: true,
                extraKeys: {
                    'Ctrl-Space': 'autocomplete',
                    'Ctrl-Enter': run,
                    'Cmd-Enter': run,
                }
            });

            document.getElementById('btn-run').addEventListener('click', run)

            function run() {
                let sql = editor.getSelection() || editor.getLine(editor.getCursor().line) || editor.getValue();
                Livewire.emitTo('sql-result', 'execute', sql)
            }

            function highlight() {
                document.querySelectorAll('pre code').forEach((block) => {
                    hljs.highlightBlock(block);
                });
            }

            Livewire.hook('element.updated', () => requestAnimationFrame(highlight))

            highlight();

            let linkStyleEl = document.getElementById('highlight-style-link');
            let defaultStyleLink = '{{ asset('highlightjs/styles/default.css') }}';
            let currentStyleLabels = document.querySelectorAll('.current-style');

            function changeStyle(key, name) {
                linkStyleEl.setAttribute('href', defaultStyleLink.replace('default.css', key + '.css'))
                currentStyleLabels.forEach(el => {
                    el.innerText = name
                });
                localStorage.setItem('selected_style', `${key}:${name}`)
            }

            let initStyle = (localStorage.getItem('selected_style') || '{{ config('highlight.default') }}:{{ config('highlight.styles.' . config('highlight.default')) }}').split(':');

            changeStyle(initStyle[0], initStyle[1]);

            document.querySelectorAll('.dropdown-style').forEach(function (el) {
                el.addEventListener('click', function (e) {
                    if (e.target.matches('.highlight-style-item a')) {
                        changeStyle(e.target.dataset.key, e.target.innerText)
                    }
                })
            })
        })();
    </script>
@endpush
