export function loadInstructorQuizReports(instructorID, courseID = null) {
  const container = document.getElementById("dynamicContent");
  container.innerHTML = "<p>Loading quiz reports...</p>";

  let url = `get_quiz_report.php?instructorID=${instructorID}`;
  if (courseID) url += `&courseID=${courseID}`;

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      if (data.success && data.quizzes.length > 0) {
        container.innerHTML = "<h2>Quiz Reports</h2>";

        data.quizzes.forEach((quiz) => {
          const quizDiv = document.createElement("div");
          quizDiv.className = "quiz-report-card";

          let submissionsHTML = "";
          if (quiz.submissions.length > 0) {
            submissionsHTML = `
              <table>
                <thead>
                  <tr>
                    <th style="border-radius: 0;">Student</th>
                    <th style="border-radius: 0;">Score</th>
                    <th style="border-radius: 0;">Total</th>
                    <th style="border-radius: 0;">Percentage</th>
                    <th style="border-radius: 0;">Submitted At</th>
                  </tr>
                </thead>
                <tbody>
                  ${quiz.submissions
                    .map(
                      (s) => `
                      <tr>
                        <td style="border-radius: 0;">${s.student_name}</td>
                        <td style="border-radius: 0;">${s.score}</td>
                        <td style="border-radius: 0;">${s.total_questions}</td>
                        <td style="border-radius: 0;">${s.percentage}%</td>
                        <td style="border-radius: 0;">${s.submitted_at}</td>
                      </tr>`
                    )
                    .join("")}
                </tbody>
              </table>
            `;
          } else {
            submissionsHTML = "<p>No student has completed this quiz yet.</p>";
          }

          quizDiv.innerHTML = `
            <h3>${quiz.title}</h3>
            <p><strong>Students Completed:</strong> ${quiz.submissions_count}</p>
            ${submissionsHTML}
          `;

          container.appendChild(quizDiv);
        });
      } else {
        container.innerHTML = "<p>No quizzes found for your courses.</p>";
      }
    })
    .catch((err) => {
      console.error("Error loading reports:", err);
      container.innerHTML = "<p>Error loading quiz reports.</p>";
    });
}
