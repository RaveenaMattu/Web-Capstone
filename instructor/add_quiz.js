export function initQuizLogic(selectedCourseID) {
    const dynamicContent = document.getElementById('dynamicContent');

    dynamicContent.innerHTML = `
        <div class="quiz-container">
            <h2 class="quiz-title" style="font-weight: 500;">Create a New Quiz</h2>
            <form id="quizForm" class="quiz-form">
                <input type="text" name="quizTitle" placeholder="Quiz Title" required class="quiz-input">
                <div class="quiz-buttons">
                    <button type="submit" class="btn btn-save" style="margin-top:0;">Add Quiz</button>
                </div>
                <h3 style="font-weight:500; margin-bottom:-5px;">Existing Quizzes</h3>
                <small>Select a quiz to add/update questions or delete.</small>
                <ul id="quizList" class="quiz-list"></ul>
                <div id="questionsContainer"></div>
                <p id="quizMsg" style="margin-top:10px;"></p>
            </form>
        </div>
    `;

    const questionsContainer = document.getElementById('questionsContainer');
    const msgP = document.getElementById('quizMsg');
    let questions = [];
    let currentQuestionIndex = 0;
    let selectedQuizID = null;

    // Fetch quizzes
    fetch(`get_quizzes.php?courseID=${selectedCourseID}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) renderQuizList(data.quizzes);
        })
        .catch(err => {
            msgP.style.color = '#aa0303ff';
            msgP.textContent = 'Error loading quizzes';
            console.error("Error loading quizzes:", err);
        });

    function renderQuizList(quizzes) {
        const quizList = document.getElementById('quizList');
        quizList.innerHTML = '';

        if (!quizzes || quizzes.length === 0) {
            quizList.innerHTML = `<p style="text-align:center; color:#666;">There are no existing quizzes.</p>`;
            return;
        }

        quizzes.forEach(quiz => {
            const li = document.createElement('li');
            li.classList.add('quiz-item');
            li.textContent = quiz.title;
            li.style.cursor = 'pointer';

            const btnGroup = document.createElement('div');
            btnGroup.style.display = 'none';
            btnGroup.style.marginTop = '5px';

            // Add / Update button
            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.textContent = quiz.questions && quiz.questions.length > 0 ? 'Update Quiz' : '+ Add Question';
            addBtn.classList.add('btn', 'btn-add');
            addBtn.style.marginRight = '10px';

            addBtn.addEventListener('click', e => {
                e.stopPropagation();
                selectedQuizID = quiz.quizID;

                // Map correctly
                questions = quiz.questions ? quiz.questions.map(q => ({
                    questionID: q.questionID,
                    question_text: q.question_text,
                    option_a: q.option_a,
                    option_b: q.option_b,
                    option_c: q.option_c,
                    option_d: q.option_d,
                    correct_option: q.correct_option
                })) : [];

                if (questions.length === 0) {
                    questions.push({
                        questionID: null,
                        question_text: '',
                        option_a: '',
                        option_b: '',
                        option_c: '',
                        option_d: '',
                        correct_option: ''
                    });
                }

                currentQuestionIndex = 0;
                renderQuestion(currentQuestionIndex);
            });

            // Delete quiz button
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.textContent = 'Delete';
            deleteBtn.classList.add('btn', 'btn-delete');
            deleteBtn.style.background = "#b50000ff";

            deleteBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (confirm('Delete this quiz?')) {
                    const formData = new FormData();
                    formData.append("quizID", quiz.quizID);

                    fetch('instructor_delete_quiz.php', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) renderQuizList(data.quizzes);
                        });
                }
            });

            btnGroup.appendChild(addBtn);
            btnGroup.appendChild(deleteBtn);
            li.appendChild(btnGroup);

            li.addEventListener('click', () => {
                document.querySelectorAll('.quiz-item').forEach(item => {
                    item.style.background = '';
                    item.querySelector('div').style.display = 'none';
                });
                li.style.background = '#edf4fb';
                btnGroup.style.display = 'block';
            });

            quizList.appendChild(li);
        });
    }

    function renderQuestion(index) {
        questionsContainer.innerHTML = '';

        if (questions.length === 0) return;

        const q = questions[index] || {};

        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-block';
        questionDiv.style.marginBottom = '15px';

        questionDiv.innerHTML = `
            <h4>Question ${index + 1}</h4>
            <textarea placeholder="Enter question" required style="width:100%; padding:5px;">${q.question_text || ''}</textarea>
            <input type="text" placeholder="Option A" required value="${q.option_a || ''}">
            <input type="text" placeholder="Option B" required value="${q.option_b || ''}">
            <input type="text" placeholder="Option C" required value="${q.option_c || ''}">
            <input type="text" placeholder="Option D" required value="${q.option_d || ''}">
            <select required>
                <option value="">Select Correct Option</option>
                <option value="a" ${q.correct_option === 'a' ? 'selected' : ''}>A</option>
                <option value="b" ${q.correct_option === 'b' ? 'selected' : ''}>B</option>
                <option value="c" ${q.correct_option === 'c' ? 'selected' : ''}>C</option>
                <option value="d" ${q.correct_option === 'd' ? 'selected' : ''}>D</option>
            </select>
            <input type="hidden" value="${q.questionID || ''}">
        `;

        // nav + save
        const navDiv = document.createElement('div');
        navDiv.style.marginTop = '10px';
        navDiv.innerHTML = `
            <div style="float:left;">
                <button type="button" id="prevQBtn" class="btn btn-nav" ${index === 0 ? 'disabled' : ''}>Previous</button>
                <button type="button" id="nextQBtn" class="btn btn-nav">${index === questions.length - 1 ? 'Add New' : 'Next'}</button>
            </div>
            <div style="float:right;">
                <button type="button" id="saveAllBtn" class="btn btn-save">Save All Questions</button>
            </div>
            <div style="clear:both;"></div>
        `;
        questionDiv.appendChild(navDiv);

        questionsContainer.appendChild(questionDiv);

        // inputs
        const textarea = questionDiv.querySelector('textarea');
        const inputs = questionDiv.querySelectorAll('input[type="text"]');
        const select = questionDiv.querySelector('select');
        const hiddenId = questionDiv.querySelector('input[type="hidden"]');

        function updateCurrent() {
            questions[index] = {
                questionID: hiddenId.value || null,
                question_text: textarea.value.trim(),
                option_a: inputs[0].value.trim(),
                option_b: inputs[1].value.trim(),
                option_c: inputs[2].value.trim(),
                option_d: inputs[3].value.trim(),
                correct_option: select.value
            };
        }

        document.getElementById('prevQBtn').onclick = () => {
            updateCurrent();
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                renderQuestion(currentQuestionIndex);
            }
        };

        document.getElementById('nextQBtn').onclick = () => {
            updateCurrent();
            if (currentQuestionIndex === questions.length - 1) {
                questions.push({ questionID: null, question_text: '', option_a: '', option_b: '', option_c: '', option_d: '', correct_option: '' });
                currentQuestionIndex++;
            } else {
                currentQuestionIndex++;
            }
            renderQuestion(currentQuestionIndex);
        };

        document.getElementById('saveAllBtn').onclick = () => {
            updateCurrent();
            saveAllQuestions();
        };
    }

    function saveAllQuestions() {
        const formData = new FormData();
        formData.append('quizID', selectedQuizID);
        formData.append('courseID', selectedCourseID);

        questions.forEach((q, i) => {
            formData.append(`question_${i + 1}_id`, q.questionID ?? '');
            formData.append(`question_${i + 1}`, q.question_text);
            formData.append(`question_${i + 1}_a`, q.option_a);
            formData.append(`question_${i + 1}_b`, q.option_b);
            formData.append(`question_${i + 1}_c`, q.option_c);
            formData.append(`question_${i + 1}_d`, q.option_d);
            formData.append(`question_${i + 1}_correct`, q.correct_option);
        });

        fetch('instructor_add_quiz_questions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                msgP.style.color = data.success ? 'green' : '#aa0303ff';
                msgP.textContent = data.message || 'Error saving questions.';
                if (data.success) {
                    questionsContainer.innerHTML = '';
                    questions = [];
                    currentQuestionIndex = 0;
                }
            })
            .catch(() => {
                msgP.style.color = '#aa0303ff';
                msgP.textContent = 'Server error saving questions.';
            });
    }

    // Add new quiz
    document.getElementById('quizForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('courseID', selectedCourseID);

        fetch('instructor_add_quiz.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) renderQuizList(data.quizzes);
                this.reset();
            });
    });
}
