<x-layouts.cpanel.settings.app-layout title="Template Preview">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">
            Preview Template: {{ $template->name }}
        </h5>
    </div>

    <div class="card-body">

        <div class="row">

            {{-- PREVIEW --}}
            <div class="col-md-6 d-flex justify-content-center mb-4 mb-md-0">
                {{-- EXISTING PREVIEW BLOCK AND CSS stays --}}

                <div class="wa-bg">

                    <div class="wa-bubble">

                        {{-- HEADER --}}
                        @foreach ($template->components as $component)
                            @if ($component->isHeader())
                                @if ($component->format === 'IMAGE')
                                    <div class="wa-header-image">
                                        <img src="{{ data_get($component->raw_component, 'example.header_handle.0') }}">
                                    </div>
                                @elseif($component->format === 'TEXT')
                                    <div class="wa-header-text">
                                        {!! preg_replace_callback(
                                            '/\{\{(\d+)\}\}/',
                                            function ($m) {
                                                return '<span class="wa-placeholder" data-section="header" data-key="' .
                                                    $m[1] .
                                                    '">&#123;&#123;' .
                                                    $m[1] .
                                                    '&#125;&#125;</span>';
                                            },
                                            e($component->text),
                                        ) !!}
                                    </div>
                                @endif
                            @endif
                        @endforeach

                        {{-- BODY --}}
                        @foreach ($template->components as $component)
                            @if ($component->isBody())
                                <div class="wa-body">
                                    {!! preg_replace_callback(
                                        '/\{\{(\d+)\}\}/',
                                        function ($m) {
                                            return '<span class="wa-placeholder" data-section="body" data-key="' .
                                                $m[1] .
                                                '">&#123;&#123;' .
                                                $m[1] .
                                                '&#125;&#125;</span>';
                                        },
                                        nl2br(e($component->text)),
                                    ) !!}
                                </div>
                            @endif
                        @endforeach

                        {{-- FOOTER --}}
                        @foreach ($template->components as $component)
                            @if ($component->isFooter())
                                <div class="wa-footer">
                                    {{ $component->text }}
                                </div>
                            @endif
                        @endforeach

                        {{-- TIME --}}
                        <div class="wa-time">
                            09:45
                        </div>

                    </div>

                    {{-- BUTTONS --}}
                    @foreach ($template->components as $component)
                        @if ($component->isButtons())
                            <div class="wa-buttons">

                                @foreach ($component->buttons as $btn)
                                    <div class="wa-button">
                                        @if ($btn->isUrl())
                                            <i class="fa fa-external-link-alt"></i>
                                        @else
                                            <i class="fa fa-reply"></i>
                                        @endif
                                        {{ $btn->text }}
                                    </div>
                                @endforeach

                            </div>
                        @endif
                    @endforeach

                </div>
            </div>

            {{-- PARAMETER PANEL --}}
            <div class="col-md-6 mt-md-0 mt-3">

                <div class="card shadow-sm">
                    <div class="card-header">
                        Parameter Examples
                    </div>

                    <div class="card-body">

                        @if (empty($examples))
                            <span class="text-muted">No example data provided by Meta.</span>
                        @else
                            @foreach ($examples as $section => $items)
                                <div class="mb-4">

                                    <strong class="text-uppercase">{{ $section }}</strong>

                                    <table class="table table-sm mt-2">
                                        <thead>
                                            <tr>
                                                <th width="120">Placeholder</th>
                                                <th>Example Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($items as $k => $v)
                                                <tr class="param-row" data-key="{{ $k }}">
                                                    <td>
                                                        <span class="badge bg-secondary text-white wa-example"
                                                            data-section="{{ $section }}"
                                                            data-key="{{ $k }}">
                                                            {{ '{' . '{' . $k . '}' . '}' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $v }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            @endforeach
                        @endif

                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="card-footer text-end">
        <a href="{{ route('whatsapptemplate.index') }}" class="btn btn-secondary btn-sm">
            Back
        </a>
    </div>

    @section('custom_styles')
        <style>
            .wa-bg {
                max-width: 420px;
                width: 100%;
                background: #e5ddd5;
                padding: 16px;
                border-radius: 14px;
            }

            .wa-bubble {
                background: white;
                border-radius: 10px;
                padding: 12px;
                font-size: 14px;
                line-height: 1.45;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                position: relative;
            }

            /* HEADER IMAGE */
            .wa-header-image img {
                width: 100%;
                max-height: 180px;
                object-fit: cover;
                border-radius: 8px;
                margin-bottom: 8px;
            }

            /* HEADER TEXT */
            .wa-header-text {
                font-weight: bold;
                margin-bottom: 6px;
            }

            /* BODY */
            .wa-body {
                white-space: normal;
                line-height: 1.45;
                font-size: 14px;
            }

            /* FOOTER */
            .wa-footer {
                text-align: left;
                font-size: 12px;
                color: #8b8b8b;
                margin-top: 6px;
            }

            /* TIME */
            .wa-time {
                font-size: 11px;
                color: #8b8b8b;
                position: absolute;
                bottom: 6px;
                right: 10px;
            }

            /* BUTTONS */
            .wa-buttons {
                margin-top: 6px;
            }

            .wa-button {
                background: white;
                border-top: 1px solid #ddd;
                text-align: center;
                padding: 8px;
                font-weight: 600;
                color: #0b93f6;
                cursor: pointer;
            }

            .wa-button i {
                margin-right: 6px;
            }

            .wa-placeholder {
                background: transparent;
                padding: 1px 3px;
                border-radius: 4px;
                transition: all .2s ease;
            }

            .wa-placeholder.active {
                background: #ffe066;
                color: #000;
                font-weight: bold;
            }

            .param-row {
                cursor: pointer;
                transition: background .2s;
            }

            .param-row:hover {
                background: #f1f3f5;
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script>
            document.querySelectorAll('.wa-example').forEach(el => {

                const key = el.dataset.key;
                const section = el.dataset.section;

                const targets = document.querySelectorAll(
                    `.wa-placeholder[data-section="${section}"][data-key="${key}"]`
                );

                // Hover highlight
                el.addEventListener('mouseenter', () => {
                    targets.forEach(t => t.classList.add('active'));
                });

                el.addEventListener('mouseleave', () => {
                    targets.forEach(t => t.classList.remove('active'));
                });

                // Click focus
                el.addEventListener('click', () => {

                    document.querySelectorAll('.wa-placeholder')
                        .forEach(p => p.classList.remove('active'));

                    targets.forEach(t => {
                        t.classList.add('active');
                        t.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    });
                });

            });
        </script>
    @endsection

</x-layouts.cpanel.settings.app-layout>
