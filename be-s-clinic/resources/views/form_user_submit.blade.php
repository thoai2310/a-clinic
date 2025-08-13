<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form['title'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f1f3f4;
            font-family: 'Google Sans', Roboto, Arial, sans-serif;
        }

        .form-container {
            max-width: 760px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .form-header {
            background: white;
            border-radius: 8px 8px 0 0;
            border-top: 10px solid #673ab7;
            padding: 24px;
            margin-bottom: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .form-title {
            font-size: 32px;
            font-weight: 400;
            color: #202124;
            margin-bottom: 8px;
            line-height: 1.25;
        }

        .form-description {
            font-size: 14px;
            color: #5f6368;
            line-height: 1.4;
            margin: 0;
        }

        .question-card {
            background: white;
            border-radius: 8px;
            margin-bottom: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            border: 1px solid #dadce0;
        }

        .question-title {
            font-size: 16px;
            font-weight: 400;
            color: #202124;
            margin-bottom: 8px;
            line-height: 1.5;
        }

        .question-description {
            font-size: 14px;
            color: #5f6368;
            margin-bottom: 16px;
            line-height: 1.4;
        }

        .required-mark {
            color: #d93025;
            margin-left: 4px;
        }

        .form-control, .form-select {
            border: 1px solid #dadce0;
            border-radius: 4px;
            font-size: 14px;
            color: #202124;
            transition: border-color 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #673ab7;
            box-shadow: 0 0 0 0.2rem rgba(103, 58, 183, 0.25);
        }

        .form-check {
            margin-bottom: 12px;
        }

        .form-check-input {
            margin-top: 0.125em;
        }

        .form-check-input:checked {
            background-color: #673ab7;
            border-color: #673ab7;
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(103, 58, 183, 0.25);
        }

        .form-check-label {
            font-size: 14px;
            color: #202124;
            line-height: 1.4;
        }

        .submit-section {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-top: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
            text-align: left;
        }

        .btn-submit {
            background-color: #673ab7;
            border-color: #673ab7;
            color: white;
            padding: 10px 24px;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.25px;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .btn-submit:hover {
            background-color: #5e35b1;
            border-color: #5e35b1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn-submit:disabled {
            background-color: #9e9e9e;
            border-color: #9e9e9e;
        }

        .other-input {
            margin-top: 8px;
            display: none;
        }

        .error-message {
            color: #d93025;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .result-view {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        .result-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px 40px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .answer-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .answer-label {
            font-weight: 600;
            color: #495057;
        }

        .answer-value {
            color: #28a745;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-container {
                margin: 20px auto;
                padding: 0 8px;
            }

            .form-header, .question-card, .submit-section {
                padding: 16px;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    @if(isset($isSubmitted) && $isSubmitted)
        {{-- View kết quả đã submit --}}
        <div class="result-view">
            <div class="result-header">
                <h1><i class="bi bi-check-circle me-2"></i>Cảm ơn bạn đã tham gia!</h1>
                <p class="mb-0">Đây là kết quả bạn đã trả lời trước đó</p>
            </div>

            <div class="form-body">
                <div class="question-card">
                    <h2 class="h4 mb-3">{{ $form['title'] }}</h2>
                    @if($form['description'])
                        <p class="text-muted">{{ $form['description'] }}</p>
                    @endif
                </div>

                @foreach($form['questions'] as $index => $question)
                    <div class="question-card">
                        <div class="question-title">
                            {{ $index + 1 }}. {{ $question['title'] }}
                            @if($question['required'])
                                <span class="required-mark">*</span>
                            @endif
                        </div>

                        @if($question['description'])
                            <div class="question-description">{{ $question['description'] }}</div>
                        @endif

                        @if(isset($answers[$question['id']]))
                            <div class="answer-item">
                                <div class="answer-label">Câu trả lời của bạn:</div>
                                <div class="answer-value">
                                    @if(is_array($answers[$question['id']]))
                                        @foreach($answers[$question['id']] as $answer)
                                            <div>• {{ $answer }}</div>
                                        @endforeach
                                    @else
                                        {{ $answers[$question['id']] }}
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="answer-item">
                                <div class="answer-value text-muted">
                                    <i>Chưa trả lời</i>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Button thực hiện lại survey -->
                <div class="question-card text-center">
                    <button type="button" class="btn btn-outline-primary" onclick="retakeSurvey()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Thực hiện lại survey
                    </button>
                </div>
            </div>
        </div>
    @else
        {{-- Form để submit --}}
        <div id="formContent">
            <!-- Form Header -->
            <div class="form-header">
                <h1 class="form-title">{{ $form['title'] }}</h1>
                @if($form['description'])
                    <p class="form-description">{{ $form['description'] }}</p>
                @endif
            </div>

            <!-- Form -->
            <form id="surveyForm">
                @csrf
                <input type="hidden" name="survey_code" value="{{ request('survey_code') }}">

                <div id="questionsContainer">
                    @foreach($form['questions'] as $index => $question)
                        <div class="question-card">
                            <div class="question-title">
                                {{ $index + 1 }}. {{ $question['title'] }}
                                @if($question['required'])
                                    <span class="required-mark">*</span>
                                @endif
                            </div>

                            @if($question['description'])
                                <div class="question-description">{{ $question['description'] }}</div>
                            @endif

                            @php $name = 'question_' . $question['id']; @endphp

                            @if($question['type'] === 'text' || $question['type'] === 'email')
                                <input type="{{ $question['type'] }}" class="form-control"
                                       name="{{ $name }}" id="{{ $name }}"
                                    {{ $question['required'] ? 'required' : '' }}>

                            @elseif($question['type'] === 'textarea')
                                <textarea class="form-control" name="{{ $name }}" id="{{ $name }}"
                                          rows="4" {{ $question['required'] ? 'required' : '' }}></textarea>

                            @elseif($question['type'] === 'number')
                                <input type="number" class="form-control"
                                       name="{{ $name }}" id="{{ $name }}"
                                    {{ $question['required'] ? 'required' : '' }}>

                            @elseif($question['type'] === 'date')
                                <input type="date" class="form-control"
                                       name="{{ $name }}" id="{{ $name }}"
                                    {{ $question['required'] ? 'required' : '' }}>

                            @elseif($question['type'] === 'select')
                                <select class="form-select" name="{{ $name }}" id="{{ $name }}"
                                    {{ $question['required'] ? 'required' : '' }}>
                                    <option value="">Chọn...</option>
                                    @foreach($question['options'] as $option)
                                        @if(!$option['isOther'])
                                            <option value="{{ $option['id'] }}">{{ $option['text'] }}</option>
                                        @endif
                                    @endforeach
                                </select>

                            @elseif($question['type'] === 'radio')
                                @foreach($question['options'] as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                               name="{{ $name }}" id="{{ $name }}_{{ $option['id'] }}"
                                               value="{{ $option['id'] }}" {{ $question['required'] ? 'required' : '' }}
                                               onchange="handleOtherOption(this)">
                                        <label class="form-check-label" for="{{ $name }}_{{ $option['id'] }}">
                                            {{ $option['text'] }}
                                        </label>
                                    </div>

                                    @if($option['isOther'])
                                        <div class="other-input" id="other_{{ $name }}_{{ $option['id'] }}">
                                            <input type="text" class="form-control form-control-sm"
                                                   name="{{ $name }}_other"
                                                   id="{{ $name }}_other_{{ $option['id'] }}"
                                                   placeholder="Vui lòng ghi rõ...">
                                        </div>
                                    @endif
                                @endforeach

                            @elseif($question['type'] === 'checkbox')
                                @foreach($question['options'] as $option)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="{{ $name }}[]" id="{{ $name }}_{{ $option['id'] }}"
                                               value="{{ $option['id'] }}"
                                               onchange="handleOtherOption(this)">
                                        <label class="form-check-label" for="{{ $name }}_{{ $option['id'] }}">
                                            {{ $option['text'] }}
                                        </label>
                                    </div>

                                    @if($option['isOther'])
                                        <div class="other-input" id="other_{{ $name }}_{{ $option['id'] }}">
                                            <input type="text" class="form-control form-control-sm"
                                                   name="{{ $name }}_other"
                                                   id="{{ $name }}_other_{{ $option['id'] }}"
                                                   placeholder="Vui lòng ghi rõ...">
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            <div class="error-message" id="error-{{ $question['id'] }}">Trường này là bắt buộc</div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Section -->
                <div class="submit-section">
                    <div class="save-indicator" id="saveIndicator" style="display: none;">
                        <small class="text-muted">
                            <i class="bi bi-cloud-check text-success me-1"></i>
                            Câu trả lời đã được lưu tự động
                        </small>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-submit" id="submitBtn">
                            <i class="bi bi-send me-2"></i>
                            Gửi
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" id="clearBtn">
                            Xóa form
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Global variables
    let formData = @json($form);
    let storageKey = 'survey_answers_{{ request("survey_code") }}';
    let autoSaveTimeout;

    // Handle "Other" option functionality
    function handleOtherOption(element) {
        const isOther = @json($form['questions']).find(q =>
            q.options && q.options.find(opt => opt.id == element.value && opt.isOther)
        );

        if (!isOther) return;

        const name = element.name.replace('[]', '');
        const optionId = element.value;
        const otherContainer = document.getElementById(`other_${name}_${optionId}`);

        if (element.type === 'radio') {
            // Hide all other inputs in radio group first
            const allRadios = document.querySelectorAll(`input[name="${element.name}"]`);
            allRadios.forEach(radio => {
                const radioOptionId = radio.value;
                const radioOtherContainer = document.getElementById(`other_${name}_${radioOptionId}`);
                if (radioOtherContainer) {
                    radioOtherContainer.style.display = 'none';
                    const input = radioOtherContainer.querySelector('input');
                    if (input) input.value = '';
                }
            });
        }

        if (otherContainer) {
            if (element.checked) {
                otherContainer.style.display = 'block';
                const input = otherContainer.querySelector('input');
                if (input) input.focus();
            } else {
                otherContainer.style.display = 'none';
                const input = otherContainer.querySelector('input');
                if (input) input.value = '';
            }
        }
    }

    // Form validation
    function validateForm() {
        let isValid = true;

        formData.questions.forEach(question => {
            if (question.required) {
                const name = `question_${question.id}`;
                const errorEl = document.getElementById(`error-${question.id}`);
                let hasValue = false;

                if (question.type === 'checkbox') {
                    const checkboxes = document.querySelectorAll(`input[name="${name}[]"]:checked`);
                    hasValue = checkboxes.length > 0;

                } else if (question.type === 'radio') {
                    const radio = document.querySelector(`input[name="${name}"]:checked`);
                    hasValue = radio !== null;

                } else {
                    const input = document.getElementById(name);
                    hasValue = input && input.value.trim() !== '';
                }

                if (!hasValue) {
                    if (errorEl) errorEl.style.display = 'block';
                    isValid = false;
                } else {
                    if (errorEl) errorEl.style.display = 'none';
                }
            }
        });

        return isValid;
    }

    // Auto-save functionality
    function saveToStorage() {
        try {
            const form = document.getElementById('surveyForm');
            if (!form) return;

            const answers = {};

            // Save text inputs, textarea, select
            form.querySelectorAll('input[type="text"], input[type="email"], input[type="number"], input[type="date"], textarea, select').forEach(input => {
                if (input.value) {
                    answers[input.name] = input.value;
                }
            });

            // Save radio buttons
            form.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
                answers[radio.name] = radio.value;

                // Save "other" text if exists
                const otherInput = document.getElementById(`${radio.name}_other_${radio.value}`);
                if (otherInput && otherInput.value) {
                    answers[`${radio.name}_other`] = otherInput.value;
                }
            });

            // Save checkboxes
            const checkboxGroups = {};
            form.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
                const name = checkbox.name.replace('[]', '');
                if (!checkboxGroups[name]) {
                    checkboxGroups[name] = [];
                }
                checkboxGroups[name].push(checkbox.value);

                // Save "other" text if exists
                const otherInput = document.getElementById(`${name}_other_${checkbox.value}`);
                if (otherInput && otherInput.value) {
                    answers[`${name}_other`] = otherInput.value;
                }
            });

            // Add checkbox arrays to answers
            Object.entries(checkboxGroups).forEach(([name, values]) => {
                answers[`${name}[]`] = values;
            });

            const saveData = {
                answers: answers,
                timestamp: new Date().toISOString(),
                formId: formData.id
            };

            localStorage.setItem(storageKey, JSON.stringify(saveData));
            showSaveIndicator();
            console.log('Auto-saved:', Object.keys(answers).length, 'fields');
        } catch (error) {
            console.error('Error saving to storage:', error);
        }
    }

    function loadFromStorage() {
        try {
            const savedData = localStorage.getItem(storageKey);
            if (!savedData) return;

            const data = JSON.parse(savedData);
            if (data.formId !== formData.id) {
                localStorage.removeItem(storageKey);
                return;
            }

            // Restore form values
            Object.entries(data.answers).forEach(([name, value]) => {
                if (name.endsWith('[]')) {
                    // Restore checkboxes
                    const baseName = name.replace('[]', '');
                    if (Array.isArray(value)) {
                        value.forEach(val => {
                            const checkbox = document.querySelector(`input[name="${baseName}[]"][value="${val}"]`);
                            if (checkbox) {
                                checkbox.checked = true;
                                handleOtherOption(checkbox);
                            }
                        });
                    }
                } else if (name.endsWith('_other')) {
                    // Restore "other" text inputs
                    const baseName = name.replace('_other', '');
                    const otherInput = document.querySelector(`input[name="${name}"]`);
                    if (otherInput) {
                        otherInput.value = value;
                        // Show the other input container
                        const container = otherInput.closest('.other-input');
                        if (container) {
                            container.style.display = 'block';
                        }
                    }
                } else {
                    // Restore text inputs, radio buttons, select
                    const input = document.querySelector(`input[name="${name}"], textarea[name="${name}"], select[name="${name}"]`);
                    if (input) {
                        if (input.type === 'radio') {
                            const radio = document.querySelector(`input[name="${name}"][value="${value}"]`);
                            if (radio) {
                                radio.checked = true;
                                handleOtherOption(radio);
                            }
                        } else {
                            input.value = value;
                        }
                    }
                }
            });

            console.log('Auto-loaded:', Object.keys(data.answers).length, 'fields');

        } catch (error) {
            console.error('Error loading from storage:', error);
            localStorage.removeItem(storageKey);
        }
    }

    function showSaveIndicator() {
        const indicator = document.getElementById('saveIndicator');
        if (indicator) {
            indicator.style.display = 'block';
            const now = new Date();
            const timeString = now.toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
            indicator.innerHTML = `
                <small class="text-muted">
                    <i class="bi bi-cloud-check text-success me-1"></i>
                    Đã lưu tự động lúc ${timeString}
                </small>
            `;
        }
    }

    // Form submission
    async function submitForm() {
        if (!validateForm()) return;

        const submitBtn = document.getElementById('submitBtn');
        // submitBtn.disabled = true;
        // submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';

        try {
            const formElement = document.getElementById('surveyForm');
            const formData = new FormData(formElement);

            const response = await fetch('{{ route("survey.submit") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    // 'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                localStorage.removeItem(storageKey);
                window.location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra khi gửi form. Vui lòng thử lại.');
            }

        } catch (error) {
            // console.error('Error submitting form:', error);
            // alert('Có lỗi xảy ra khi gửi form. Vui lòng thử lại.');
        } finally {
            // submitBtn.disabled = false;
            // submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Gửi';
        }
    }

    // Retake survey functionality
    async function retakeSurvey() {
        if (confirm('Bạn có chắc chắn muốn thực hiện lại survey? Các câu trả lời trước đó sẽ bị xóa.')) {
            try {
                const response = await fetch('{{ route("survey.retake") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        survey_code: '{{ request("survey_code") }}'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Clear localStorage
                    localStorage.removeItem(storageKey);
                    // Reload page to show form again
                    window.location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại.');
                }

            } catch (error) {
                console.error('Error retaking survey:', error);
                alert('Có lỗi xảy ra. Vui lòng thử lại.');
            }
        }
    }

    // Clear form
    function clearForm() {
        if (confirm('Bạn có chắc chắn muốn xóa toàn bộ dữ liệu đã nhập?')) {
            const form = document.getElementById('surveyForm');
            if (form) form.reset();

            document.querySelectorAll('.error-message').forEach(el => {
                el.style.display = 'none';
            });

            document.querySelectorAll('.other-input').forEach(el => {
                el.style.display = 'none';
                const input = el.querySelector('input');
                if (input) input.value = '';
            });

            localStorage.removeItem(storageKey);
            document.getElementById('saveIndicator').style.display = 'none';
        }
    }

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        @if(!isset($isSubmitted) || !$isSubmitted)
        // Load saved data
        loadFromStorage();

        // Bind events
        const surveyForm = document.getElementById('surveyForm');
        if (surveyForm) {
            surveyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitForm();
            });

            // Auto-save on input
            surveyForm.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(saveToStorage, 1000);
            });

            surveyForm.addEventListener('change', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(saveToStorage, 500);
            });
        }

        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', clearForm);
        }

        // Auto-save every 30 seconds
        setInterval(saveToStorage, 30000);

        // Save before page unload
        window.addEventListener('beforeunload', saveToStorage);
        @endif
    });
</script>
</body>
</html>
