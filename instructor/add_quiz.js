export function initQuizLogic(selectedCourseID) {
    const dynamicContent = document.getElementById('dynamicContent');

    dynamicContent.innerHTML = `
        <div class="quiz-container">
            <h2 class="quiz-title" style="font-weight: 500;">Create a New Quiz</h2>
            <form id="quizForm" class="quiz-form">
                <input type="text" name="quizTitle" placeholder="Quiz Title" required class="quiz-input">
                <div class="quiz-buttons">
                    <button type="submit" class="btn btn-save" style="margin-top: 0;">Add Quiz</button>
                </div>
                <h3 style="font-weight: 500; margin-bottom: -5px;">Existing Quizzes</h3>
                <small>Select a quiz to add questions or delete.</small>
                <ul id="quizList" class="quiz-list"></ul>
                <div id="questionsContainer"></div>
            </form>
        </div>
    `;

    const questionsContainer = document.getElementById('questionsContainer');
    let questionCount = 0;
    let questions = [];
    let currentIndex = 0;
    let selectedQuizID = null;

    // Fetch quizzes from server
    fetch(`get_quizzes.php?courseID=${selectedCourseID}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) renderQuizList(data.quizzes);
        })
        .catch(err => console.error("Error loading quizzes:", err));

    // Render quiz list
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

            const addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.textContent = '+ Add Question';
            addBtn.classList.add('btn', 'btn-add');
            addBtn.style.marginRight = '10px';
            addBtn.addEventListener('click', e => {
                e.stopPropagation();
                selectedQuizID = quiz.quizID;
                addQuestion();
            });

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

                    fetch('instructor_delete_quiz.php', {
                        method: 'POST',
                        body: formData
                    })
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

    function addQuestion() {
        questionCount++;
        questions.push({ questionID: questionCount });

        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-block';
        questionDiv.style.marginBottom = '15px';
        questionDiv.innerHTML = `
            <h4>Question ${questionCount}</h4>
            <textarea name="question_${questionCount}" placeholder="Enter question" required style="width:100%; padding:5px;"></textarea>
            <input type="text" name="question_${questionCount}_a" placeholder="Option A" style="margin-bottom:5px;" required>
            <input type="text" name="question_${questionCount}_b" placeholder="Option B" style="margin-bottom:5px;" required>
            <input type="text" name="question_${questionCount}_c" placeholder="Option C" style="margin-bottom:5px;" required>
            <input type="text" name="question_${questionCount}_d" placeholder="Option D" style="margin-bottom:5px;" required>
            <select name="question_${questionCount}_correct" required>
                <option value="">Select Correct Option</option>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
                <option value="d">D</option>
            </select>
            <div style="display:flex; justify-content:space-between; margin-top:10px;">
                <div>
                    <button type="button" id="prevQBtn" class="btn btn-nav">Previous</button>
                    <button type="button" id="nextQBtn" class="btn btn-nav">Next</button>
                </div>
                <div>
                    <button type="button" id="saveAllBtn" class="btn btn-save">Save All Questions</button>
                </div>
            </div>
        `;
        questionsContainer.appendChild(questionDiv);

        const saveAllBtn = questionDiv.querySelector('#saveAllBtn');
        saveAllBtn.addEventListener('click', () => saveQuestions());
    }

    function saveQuestions() {
        const formData = new FormData();
        formData.append('quizID', selectedQuizID);
        formData.append('courseID', selectedCourseID);

        questionsContainer.querySelectorAll('.question-block').forEach((div, i) => {
            formData.append(`question_${i + 1}`, div.querySelector(`textarea`).value);
            formData.append(`question_${i + 1}_a`, div.querySelector(`input[name$="_a"]`).value);
            formData.append(`question_${i + 1}_b`, div.querySelector(`input[name$="_b"]`).value);
            formData.append(`question_${i + 1}_c`, div.querySelector(`input[name$="_c"]`).value);
            formData.append(`question_${i + 1}_d`, div.querySelector(`input[name$="_d"]`).value);
            formData.append(`question_${i + 1}_correct`, div.querySelector('select').value);
        });

        fetch('instructor_add_quiz_questions.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert('Questions saved successfully');
                    questionsContainer.innerHTML = '';
                }
            });
    }

    // Handle adding new quiz
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
