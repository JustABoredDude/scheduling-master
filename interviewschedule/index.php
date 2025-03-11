<?php
session_start();
require_once 'db.php';
require_once 'functions.php';

$selected_program = $_GET['program'] ?? null;
$status_filter = $_GET['status'] ?? 'scheduled';
$search = $_GET['search'] ?? '';

$sort = $_GET['sort'] ?? 'newest'; 
$status_filter = ($_GET['status'] ?? 'scheduled') === 'date' ? 'date' : $_GET['status'];
$interviews = getInterviews($conn, $selected_program, $status_filter, $search, $sort);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Scheduling</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="half-w-6xl w-full bg-white p-6 shadow-lg rounded-lg">
    <!-- Header -->
    <header class="border-b border-gray-300 pb-4">
    <h1 class="text-2xl font-bold text-green-700">Interview Scheduling</h1>

    <div class="flex items-center gap-4 mt-2">  <!-- Add Schedule & Search -->
        <a href="add_interview.php" class="px-4 py-2 bg-green-100 text-green-700 border border-green-600 rounded-full hover:bg-green-200 flex items-center justify-center gap-2">
            <span>Add Schedule</span>
            <span>+</span>
        </a>

        <form id="searchForm" method="GET" action="index.php" class="flex items-center">
            <div class="relative rounded-full shadow-md">
                <input type="text" name="search" class="rounded-full border border-gray-300 py-2 pl-5 pr-10 focus:outline-none w-64" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="absolute right-0 top-0 h-full px-4 rounded-full bg-transparent hover:bg-gray-100 text-gray-600">
                    <img src="assets/search.png" alt="Search" class="w-5 h-5">
                </button>
            </div>
        </form>
    </div>
</header>



    <!-- Filters -->
    <div class="flex flex-col sm:flex-row justify-between items-center mt-4 mb-6 gap-4">
        <div class="flex gap-2">
            <select id="program" class="border rounded-lg px-3 py-2" onchange="updateFilter('program', this.value)">
                <option value="">All programs</option>
                <option value="CBA" <?= ($selected_program == 'CBA') ? 'selected' : '' ?>>CBA</option>
                <option value="COT" <?= ($selected_program == 'COT') ? 'selected' : '' ?>>COT</option>
                <option value="COED" <?= ($selected_program == 'COED') ? 'selected' : '' ?>>COED</option>
            </select>

            <select id="status" class="border rounded-lg px-3 py-2" onchange="updateFilter('status', this.value)">
                <option value="scheduled" <?= ($status_filter == 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                <option value="cancelled" <?= ($status_filter == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                <option value="date" <?= ($status_filter == 'date') ? 'selected' : '' ?>>Date</option>
                <option value="inverviewer" <?= ($status_filter == 'interviewer') ? 'selected' : '' ?>>Interviewer</option>
                <option value="program" <?= ($status_filter == 'program') ? 'selected' : '' ?>>Program</option>
                <option value="trash" <?= ($status_filter == 'trash') ? 'selected' : '' ?>>Trash</option>
            </select>
        </div>

        <select id="filter" class="border rounded-lg px-3 py-2">
            <option value="" disabled selected>Sort</option>
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="alphabetical">A-Z</option>
            <option value="reversalphabetical">Z-A</option>
        </select>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse bg-white shadow-md rounded-lg">
            <thead class="<?= ($status_filter == 'trash') ? 'bg-red-500 text-white' : 'bg-green-600 text-white'; ?>">

                <tr>
                    <th class="p-3 text-left">Applicant Name</th>
                    <th class="p-3 text-left"><?= ($status_filter == 'trash') ? 'Date Cancelled' : 'Date & Time'; ?></th>
                    <th class="p-3 text-left">Faculty/Interviewer</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($interviews)): ?>
                    <?php foreach ($interviews as $interview): ?>
                        <tr class="border-b hover:bg-gray-100">
                            <td class="p-3"><?= htmlspecialchars($interview['applicant'] ?? 'N/A'); ?></td>
                            <td class="p-3"><?= ($status_filter == 'trash') 
                                ? date('F j, Y', strtotime($interview['cancelled_date'] ?? 'now')) 
                                : date('h:i A', strtotime($interview['scheduled_time'])) . ' - ' . date('h:i A', strtotime($interview['scheduled_time'] . ' +30 minutes')); ?>
                            </td>
                            <td class="p-3"><?= htmlspecialchars($interview['interviewer'] ?? 'N/A'); ?></td>
                            <td class="p-3 font-semibold <?= ($status_filter == 'trash' && $interview['status'] !== 'cancelled') ? 'text-red-600' : (($interview['status'] === 'cancelled') ? 'text-red-600' : 'text-green-600'); ?>">
                                <?= ucfirst($interview['status'] ?? 'Unknown'); ?>
                            </td>
                            <td class="p-3 flex space-x-2">
                                <?php if ($status_filter === 'trash'): ?>
                                    <button onclick="restoreInterview(<?= $interview['id']; ?>)" class="px-3 py-1 bg-green-400 text-white rounded-lg hover:bg-green-600">
                                    <img src="assets/restore.png" alt="Restore" title="Restore" class="w-4 h-4">
                                    </button>
                                    <button onclick="deleteInterview(<?= $interview['id']; ?>)" class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600">
                                    <img src="assets/delete.png" alt="Delete" title="Delete" class="w-4 h-4">
                                    </button>
                                <?php elseif ($status_filter === 'cancelled' || ($status_filter === 'date' && $interview['status'] === 'cancelled')): ?>
                                    <a href="edit_interview.php?id=<?= $interview['id']; ?>" class="px-3 py-1 bg-blue-400 text-white rounded-lg hover:bg-blue-400">
                                        <img src="assets/edit.png" alt="Edit" title="Edit" class="w-4 h-4"></a>
                                    <a href="view_interview.php?id=<?= $interview['id']; ?>" class="px-3 py-1 bg-green-400 text-white rounded-lg hover:bg-green-400">
                                    <img src="assets/view.png" alt="View" title="View" class="w-4 h-4">
                                    </a>
                                    <button onclick="moveToTrash(<?= $interview['id']; ?>)" class="px-3 py-1 bg-red-400 text-white rounded-lg hover:bg-red-600">
                                    <img src="assets/delete.png" alt="Delete" title="Delete" class="w-4 h-4">
                                    </button>
                                <?php else: ?>
                                    <a href="edit_interview.php?id=<?= $interview['id']; ?>" class="px-3 py-1 bg-blue-400 text-white rounded-lg hover:bg-blue-400">
                                    <img src="assets/edit.png" alt="Edit" title="Edit" class="w-4 h-4">
                                    </a>
                                    <a href="view_interview.php?id=<?= $interview['id']; ?>" class="px-3 py-1 bg-green-400 text-white rounded-lg hover:bg-green-400">
                                    <img src="assets/view.png" alt="View" title="View" class="w-4 h-4">
                                    </a>
                                    <button onclick="cancelInterview(<?= $interview['id']; ?>)" class="px-3 py-1 bg-red-400 text-white rounded-lg hover:bg-red-400">
                                    <img src="assets/cancel.png" alt="Cancel" title="Cancel" class="w-4 h-4">
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 p-3">No interviews found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function updateFilter(type, value) {
        const url = new URL(window.location.href);
        url.searchParams.set(type, value);
        window.location.href = url.toString();
    }

    function cancelInterview(interviewId) {
        if (confirm("Are you sure you want to cancel this interview?")) {
            window.location.href = `cancel_interview.php?id=${interviewId}`;
        }
    }

    function deleteInterview(interviewId) {
    if (confirm("Are you sure you want to delete this interview? This interview will cannot be stored")) {
        fetch(`delete_interview.php?id=${interviewId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Interview deleted successfully!');
                window.location.reload(); // Reload the page to reflect the changes
            } else {
                alert('Error deleting interview: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Request failed.');
            });
        }
    }

    function moveToTrash(interviewId) {
        if (confirm("Are you sure you want to move this interview to trash? You can still restore it if you want to.")) {
            fetch(`trash_interview.php?id=${interviewId}`, {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Interview moved to trash successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Request failed.');
            });
        }
    }

    function restoreInterview(interviewId) {
    if (confirm("Are you sure you want to restore this interview?")) {
        fetch(`restore_interview.php?id=${interviewId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Interview restored successfully!');
                window.location.reload(); // Reload the page to update the table
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Request failed.');
            });
        }
    }


    
    document.getElementById('searchForm').addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            this.submit();
        }
    });
</script>

</body>
</html>