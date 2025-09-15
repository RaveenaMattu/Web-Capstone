export function loadStudentQuizzes(studentID, courseID) {
  const container = document.getElementById("dynamicContent");
  container.innerHTML = "<p>Loading quizzes...</p>";
  container.style.display = "block";

  fetch(`get_student_quizzes.php?studentID=${studentID}&courseID=${courseID}`)
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.quizzes.length > 0) {
        container.innerHTML =
          '<h2 style="text-align:center; font-weight: 500;">Available Quizzes</h2>';

        data.quizzes.forEach((quiz) => {
          const quizDiv = document.createElement("div");
          quizDiv.className = "quiz-card";

          // Build card
          quizDiv.innerHTML = `
            <h3>${quiz.title}</h3>
            <button class="btn btn-start">Start Quiz</button>
            <div class="quiz-questions" style="display:none;"></div>
            <div class="quiz-result" style="margin-top:10px; font-weight:400; color: #d77825ff;"></div>`;

          const btn = quizDiv.querySelector(".btn-start");
          const questionsDiv = quizDiv.querySelector(".quiz-questions");
          const resultDiv = quizDiv.querySelector(".quiz-result");

          // If already submitted, disable quiz
          if (quiz.submissionID) {
            btn.disabled = true;
            btn.textContent = "Already Submitted";
            resultDiv.textContent = `Score: ${quiz.score} / ${quiz.total_questions} (${quiz.percentage}%)`;
            resultDiv.style.color = "#2a7a2aff";
          } else {
            // Normal Start flow
            btn.addEventListener("click", () => {
              questionsDiv.style.display = "block";
              btn.style.display = "none";
              questionsDiv.innerHTML = "";

              quiz.questions.forEach((q, i) => {
                const qDiv = document.createElement("div");
                qDiv.style.margin = "15px auto";
                qDiv.innerHTML = `
                <p><strong>Q${i + 1}:</strong> ${q.question_text}</p>
                <label style="margin: 20px 40px;"><input type="radio" name="q${q.questionID}" value="a" style="margin-top: 15px"> ${q.option_a}</label><br>
                <label style="margin: 20px 40px;"><input type="radio" name="q${q.questionID}" value="b" style="margin-top: 15px"> ${q.option_b}</label><br>
                <label style="margin: 20px 40px;"><input type="radio" name="q${q.questionID}" value="c" style="margin-top: 15px"> ${q.option_c}</label><br>
                <label style="margin: 20px 40px;"><input type="radio" name="q${q.questionID}" value="d" style="margin-top: 15px"> ${q.option_d}</label>`;
                questionsDiv.appendChild(qDiv);
              });

              // Submit button
              const submitBtn = document.createElement("button");
              submitBtn.textContent = "Submit Quiz";
              submitBtn.className = "btn btn-submit";
              submitBtn.style.float = "right";
              submitBtn.style.marginTop = "0";
              questionsDiv.appendChild(submitBtn);

              submitBtn.addEventListener("click", () => {
                let score = 0;
                let total = quiz.questions.length;

                quiz.questions.forEach((q) => {
                  const selected = quizDiv.querySelector(
                    `input[name="q${q.questionID}"]:checked`
                  );
                  if (selected && selected.value === q.correct_option) {
                    score++;
                  }
                });

                // Save submission
                fetch("student_submit_quiz.php", {
                  method: "POST",
                  headers: { "Content-Type": "application/json" },
                  body: JSON.stringify({
                    quizID: quiz.quizID,
                    studentID: studentID,
                    score: score,
                    total: total,
                  }),
                })
                  .then((res) => res.json())
                  .then((resp) => {
                    if (resp.success) {
                      resultDiv.textContent = `You scored ${resp.score} out of ${resp.total} : (${resp.percentage}%)`;
                      resultDiv.style.color = "#2a7a2aff";
                      submitBtn.disabled = true;
                      btn.disabled = true;
                      btn.textContent = "Submitted";
                      quizDiv
                        .querySelectorAll("input")
                        .forEach((input) => (input.disabled = true));
                    } else {
                      resultDiv.textContent =
                        resp.message || "Error submitting quiz.";
                      resultDiv.style.color = "#b00000ff";
                    }
                  })
                  .catch((err) => {
                    console.error("Submit error:", err);
                    resultDiv.textContent =
                      "Server error while submitting quiz.";
                    resultDiv.style.color = "#b00000ff";
                  });
              });
            });
          }

          container.appendChild(quizDiv);
        });
      } else {
        container.innerHTML = "<p>No quizzes available for your courses.</p>";
      }
    })
    .catch((err) => {
      container.innerHTML = "<p>Error loading quizzes.</p>";
      console.error("Error loading quizzes:", err);
    });
}
