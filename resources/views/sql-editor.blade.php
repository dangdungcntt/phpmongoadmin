@extends('layouts.app')

@section('page_title', $currentConnection->name . ': ' . $currentDatabase . (isset($currentCollection) ? '.'. $currentCollection : ''))

@include('partials.connection-databases')

@push('content')
    <p class="mb-2">Run SQL query on {{ isset($currentCollection) ? 'collection' : 'database' }}: <strong style="color: {{ $currentConnection->color }}">{{ $currentDatabase }}{{ isset($currentCollection) ? ".{$currentCollection}" : '' }}</strong></p>
    <textarea id="code" name="code">{{ $sql }}</textarea>
    <div class="mt-4">
        <livewire:sql-result :sql="$sql" :connectionId="$currentConnection->id" :database="$currentDatabase"/>
    </div>
@endpush

@push('styles')
    <link rel="icon" href="{{ route('connections.favicon', $currentConnection) }}" type="image/png" sizes="20x20">
    <link rel="stylesheet" href="{{ asset('codemirror/lib/codemirror.css') }}">
    <link rel="stylesheet" href="{{ asset('codemirror/addon/hint/show-hint.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('codemirror/lib/codemirror.js') }}"></script>
    <script src="{{ asset('codemirror/addon/edit/matchbrackets.js') }}"></script>
    <script src="{{ asset('codemirror/mode/sql/sql.js') }}"></script>
    <script src="{{ asset('codemirror/addon/hint/show-hint.js') }}"></script>
    <script src="{{ asset('codemirror/addon/hint/sql-hint.js') }}"></script>
    <script>
        window.editor = CodeMirror.fromTextArea(document.getElementById('code'), {
            mode: 'text/x-mariadb',
            indentWithTabs: true,
            smartIndent: true,
            lineNumbers: true,
            matchBrackets: true,
            autofocus: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-Enter": function (editor) {
                    let sql = editor.getSelection() || editor.getLine(editor.getCursor().line) || editor.getValue();
                    Livewire.emitTo('sql-result', 'execute', sql)
                }
            }
        });
    </script>
@endpush
