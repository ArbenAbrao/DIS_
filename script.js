document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("studentTableBody").addEventListener("click", function (event) {
        const target = event.target;
        const row = target.closest("tr");
        if (!row) return;

        const studentId = row.dataset.id;
        if (!studentId) {
            console.error("Error: Student ID not found.");
            return;
        }

        if (target.classList.contains("in-btn")) {
            markAttendance(studentId, "in", row.querySelector(".time-in"));
        } else if (target.classList.contains("out-btn")) {
            markAttendance(studentId, "out", row.querySelector(".time-out"));
        } else if (target.classList.contains("delete-btn")) {
            deleteStudent(studentId, row);
        }
    });

    function markAttendance(studentId, action, timeCell) {
        fetch("attendance_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=${action}&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            console.log("Server Response:", data);
            if (data.success) {
                timeCell.textContent = data.time;  // Update table
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Fetch Error:", error));
    }

    function deleteStudent(studentId, row) {
        if (!confirm("Are you sure you want to delete this student?")) return;

        fetch("attendance_action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `action=delete&student_id=${studentId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                row.remove();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }

    function markAttendance(studentId, action, timeCell) {
    fetch("attendance_action.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=${action}&student_id=${studentId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data);
        if (data.success) {
            timeCell.textContent = data.time; // AM/PM format maintained
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Fetch Error:", error));
}

const toggleBtn = document.querySelector(".toggle-btn");
    const sidebar = document.querySelector(".sidebar");

    toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("hidden");
    });

});

