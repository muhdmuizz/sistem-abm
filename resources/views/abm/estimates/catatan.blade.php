<section class="grid-notes" aria-labelledby="grid-notes-title">
    <h2 id="grid-notes-title">Catatan</h2>
    @if($editable)
        <ul>
            <li>Gunakan <kbd>Tab</kbd> atau <kbd>Shift+Tab</kbd> untuk bergerak antara sel input.</li>
            @if($grouped)
                <li>Tampal nilai mengikut turutan lajur input dalam jadual komponen.</li>
            @else
                <li>Tampal nilai mengikut turutan lajur input berikut: {{ implode(', ', $inputLabels) }}.</li>
            @endif
        </ul>
    @endif
    <p>{{ $inputHelp }}</p>
</section>
