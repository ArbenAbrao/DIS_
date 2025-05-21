<?php 
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Attendance System</title>
    <link rel="stylesheet" href="main.css" />
    <link rel="shortcut icon" href="img/logo.png" />
</head>
<body>

<!-- Header -->
<header class="main-header">
    <div class="nav-left">
        <button class="toggle-btn">☰</button>
        <a href="index.php" class="main-header__brand">Attendance</a>
    </div>
    <nav class="main-nav">
        <ul class="main-nav__items">
            <button id="exportAttendanceBtn" class="nav-btn">📤 Export Attendance</button>
            <button id="promoteStudentsBtn" class="nav-btn">Promote Students</button>
            <button id="historyBtn" class="nav-btn">History</button>
            <a class="nav-btn" href="index.php">Home</a>
            <button class="nav-btn" onclick="openLogoutModal()">Log Out</button>
            </ul>
    </nav>
</header>

<!-- Main Container -->
<div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
        <label for="gradeSelect" class="sidebar-label">Select Employee/Grade Level:</label>
        <select id="gradeSelect" onchange="loadGradeAttendance()">
            <option value="">- Select Department -</option>
            <optgroup label="Grade Levels">
                <?php
                echo '<option value="Nursery">Nursery</option>';
                echo '<option value="Kinder">Kinder</option>';
                foreach (range(1, 12) as $grade) {
                    echo "<option value=\"Grade $grade\">Grade $grade</option>";
                }
                ?>
            </optgroup>
            <optgroup label="Employee">
                <option value="Admin">Admin</option>
                <option value="Faculty">Faculty</option>
                <option value="Guard">Guards</option>
            </optgroup>
        </select>
    </aside>

    <!-- Content -->
    <div class="content">
        <div id="attendance-section">
            <div class="top-controls">
                <button id="addStudentBtn" class="nav-btn" style="display: none;" onclick="openAddStudentModal()">+ Add Names</button>
                <input type="text" id="searchBar" class="searchBar" style="display: none;" onkeyup="searchStudents()" placeholder="Search student...">
            </div>
            <div id="attendance-content">
                <table id="studentTable">
                    <thead><tr></tr></thead>
                    <tbody id="studentTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div id="addStudentModal" class="addstudentmodal">
    <div class="addstudentmodal-content">
        <span class="close-btn" onclick="closeAddStudentModal()">&times;</span>
        <h2>Add Multiple Students</h2>
        <form id="addStudentForm">
            <div id="studentInputs">
                <div class="student-input">
                    <input type="text" name="studentNames[]" class="addstudentmodal-input" placeholder="Enter student name" required>
                    <button type="button" class="remove-btn" onclick="removeStudentInput(this)">×</button>
                </div>
            </div>
            <button type="button" onclick="addStudentInput()" class="nav-btn">Add More</button>
            <input type="hidden" id="selectedGrade">
            <button type="submit" class="nav-btn">Save All</button>
        </form>
    </div>
</div>

<!-- History Modal -->
<div id="historyModal" class="history-modal">
    <div class="history-modal-content">
        <span class="close-btn" onclick="closeHistoryModal()">&times;</span>
        <h2>Attendance History</h2>

        <div class="history-controls">
            <input type="text" id="historySearchBar" placeholder="Search history...">
            <select id="historyGradeSelect">
                <option value="">All Grades</option>
                <option value="Nursery">Nursery</option>
                <option value="Kinder">Kinder</option>
                <?php foreach (range(1, 12) as $grade): ?>
                    <option value="Grade <?= $grade ?>">Grade <?= $grade ?></option>
                <?php endforeach; ?>
            </select>
            <select id="historyEmployeeSelect">
                <option value="">All Employees</option>
                <option value="Admin">Admin</option>
                <option value="Faculty">Faculty</option>
                <option value="Guard">Guards</option>
            </select>
            <input type="date" id="historyDatePicker">
        </div>

        <div id="historyContent">
            <p>Enter a name or pick a date to view history.</p>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
background-color: #38a169; color: white; padding: 10px 20px; border-radius: 8px;
box-shadow: 0 2px 6px rgba(0,0,0,0.2); display: none; z-index: 9999;">
  Exported successfully!
</div>


<!-- Logout Modal -->
<div id="logoutModal" class="logout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 1000;">
    <div style="background: white; padding: 20px; border-radius: 8px; max-width: 300px; width: 80%; text-align: center;">
        <h3>Are you sure you want to log o  ut?</h3>
        <button onclick="confirmLogout()" class="nav-btn">Yes</button>
        <button onclick="closeLogoutModal()" class="nav-btn" style="margin-left: 10px;">Cancel</button>
    </div>
</div>

<!-- Scripts -->
<script>
function openLogoutModal() {
    document.getElementById("logoutModal").style.display = "flex";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").style.display = "none";
}

function confirmLogout() {
    window.location.href = "logout.php";
}

document.getElementById("promoteStudentsBtn").addEventListener("click", () => {
    if (confirm("Are you sure you want to promote students?")) {
        fetch("promote_students.php", { method: "POST" })
            .then(res => res.json())
            .then(data => {
                alert(data.success ? "Updated!" : "Error: " + data.message);
                if (data.success) loadGradeAttendance();
            })
            .catch(console.error);
    }
});

function addStudentInput() {
    const container = document.getElementById("studentInputs");
    const newInput = document.createElement("div");
    newInput.classList.add("student-input");
    newInput.innerHTML = `
        <input type="text" name="studentNames[]" class="addstudentmodal-input" placeholder="Enter student name" required>
        <button type="button" class="remove-btn" onclick="removeStudentInput(this)">×</button>`;
    container.appendChild(newInput);
}

function removeStudentInput(button) {
    button.parentElement.remove();
}

document.getElementById("addStudentForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append("grade", document.getElementById('selectedGrade').value);

    fetch("add_student.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeAddStudentModal();
                loadGradeAttendance();
            }
        })
        .catch(console.error);
});

function loadGradeAttendance() {
    const grade = document.getElementById('gradeSelect').value;
    const addBtn = document.getElementById('addStudentBtn');
    const searchBar = document.getElementById('searchBar');
    document.getElementById('selectedGrade').value = grade;

    if (grade) {
        fetch("attendance.php?grade=" + encodeURIComponent(grade))
            .then(res => res.text())
            .then(data => {
                document.getElementById('studentTableBody').innerHTML = data;
            });
        addBtn.style.display = "block";
        searchBar.style.display = "block";
    } else {
        addBtn.style.display = "none";
        searchBar.style.display = "none";
        document.getElementById('studentTableBody').innerHTML = "";
    }
}

function searchStudents() {
    const input = document.getElementById('searchBar').value.toLowerCase();
    const rows = document.querySelectorAll("#studentTableBody tr");
    rows.forEach(row => {
        const name = row.cells[0]?.innerText.toLowerCase() || "";
        row.style.display = name.includes(input) ? "" : "none";
    });
}

function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = "flex";
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = "none";
}

document.getElementById("historyBtn").addEventListener("click", () => {
    document.getElementById("historyModal").style.display = "flex";
    document.getElementById("historyContent").innerHTML = "<p>Enter a name or pick a date to view history.</p>";
    document.getElementById("historySearchBar").value = "";
});

function closeHistoryModal() {
    document.getElementById("historyModal").style.display = "none";
}

document.getElementById("historySearchBar").addEventListener("keydown", function (e) {
    if (e.key === "Enter") fetchHistory();
});
document.getElementById("historyGradeSelect").addEventListener("change", fetchHistory);
document.getElementById("historyEmployeeSelect").addEventListener("change", fetchHistory);
document.getElementById("historyDatePicker").addEventListener("change", fetchHistory);

function fetchHistory() {
    const search = document.getElementById("historySearchBar").value.trim();
    const grade = document.getElementById("historyGradeSelect").value;
    const employee = document.getElementById("historyEmployeeSelect").value;
    const date = document.getElementById("historyDatePicker").value;

    const params = new URLSearchParams({ search, grade, type: employee, date });

    fetch(`history.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById("historyContent");

            if (!Array.isArray(data)) {
                container.innerHTML = "<p>Error fetching data.</p>";
                return;
            }

            if (data.length === 0) {
                container.innerHTML = "<p>No records found.</p>";
                return;
            }

            let html = `
                <div class="scrollable-table">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Grade/Type</th>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                            </tr>
                        </thead>
                        <tbody>`;

            data.forEach(row => {
                html += `
                    <tr>
                        <td>${row.name}</td>
                        <td>${row.grade_level}</td>
                        <td>${row.date}</td>
                        <td>${row.time_in_am || "-"}</td>
                        <td>${row.time_out_pm || "-"}</td>
                    </tr>`;
            });

            html += `
                        </tbody>
                    </table>
                </div>`;
            container.innerHTML = html;
        })
        .catch(error => {
            console.error(error);
            document.getElementById("historyContent").innerHTML = "<p>Error loading data.</p>";
        });
}


document.getElementById("exportAttendanceBtn").addEventListener("click", () => {
    if (confirm("Export today's attendance to Excel?")) {
        fetch("export.php")
            .then(res => res.text())
            .then(responseText => {
                showToast("✅ Exported successfully!");
            })
            .catch(error => {
                showToast("❌ Export failed. Please try again.");
                console.error(error);
            });
    }
});

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.style.display = "block";
    setTimeout(() => {
        toast.style.display = "none";
    }, 3000);
}




</script>

<script src="script.js"></script>

<!-- Footer -->
<footer class="footer-container">
    <small><i>@2025 DIS</i></small>
</footer>
</body>
</html>
