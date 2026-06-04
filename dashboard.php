<?php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Welcome back, <?= $currentUser ?> 👋</h1>
    <p class="page-subtitle"><?= date('l, F j, Y') ?> &mdash; Here's your study overview</p>
  </div>
  <a href="addtask.php" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i> Add Task
  </a>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4" id="statsRow">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon indigo"><i class="bi bi-journal-bookmark-fill"></i></div>
      <div class="stat-value" id="statSubjects">—</div>
      <div class="stat-label">Subjects</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon cyan"><i class="bi bi-list-task"></i></div>
      <div class="stat-value" id="statTasks">—</div>
      <div class="stat-label">Total Tasks</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon green"><i class="bi bi-check-circle-fill"></i></div>
      <div class="stat-value" id="statCompleted">—</div>
      <div class="stat-label">Completed</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon amber"><i class="bi bi-calendar-week-fill"></i></div>
      <div class="stat-value" id="statSchedule">—</div>
      <div class="stat-label">Study Sessions</div>
    </div>
  </div>
</div>

<!-- Progress bar -->
<div class="card mb-4">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-600" style="font-size:.875rem">Overall Task Progress</span>
      <span class="fw-700" style="color:#4f46e5;font-size:.875rem" id="progressLabel">0%</span>
    </div>
    <div class="progress" style="height:8px;border-radius:999px;background:#e0e7ff">
      <div class="progress-bar" id="progressBar" role="progressbar"
           style="border-radius:999px;background:linear-gradient(90deg,#4f46e5,#818cf8)"
           style="width:0%"></div>
    </div>
  </div>
</div>

<!-- Recent Tasks + Today's Schedule -->
<div class="row g-4">
  <!-- Recent Tasks -->
  <div class="col-lg-7">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2 text-primary"></i>Recent Tasks</span>
        <a href="addtask.php" class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.78rem">View All</a>
      </div>
      <div class="card-body" id="recentTasksList">
        <div class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm" role="status"></div>
          <p class="mt-2 mb-0 small">Loading tasks…</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Schedule -->
  <div class="col-lg-5">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-day me-2 text-warning"></i>Today's Schedule</span>
        <a href="schedule.php" class="btn btn-sm btn-outline-primary" style="border-radius:7px;font-size:.78rem">Full Schedule</a>
      </div>
      <div class="card-body" id="todayScheduleList">
        <div class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm" role="status"></div>
          <p class="mt-2 mb-0 small">Loading schedule…</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
const TODAY_NAME = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][new Date().getDay()];

async function loadDashboard() {
  // Load profile stats
  const profile = await fetch('api/profile.php').then(r => r.json());
  if (profile.success) {
    const d = profile.data;
    document.getElementById('statSubjects').textContent  = d.total_subjects;
    document.getElementById('statTasks').textContent     = d.total_tasks;
    document.getElementById('statCompleted').textContent = d.completed_tasks;
    document.getElementById('statSchedule').textContent  = d.schedule_entries;

    const pct = d.total_tasks > 0 ? Math.round((d.completed_tasks / d.total_tasks) * 100) : 0;
    document.getElementById('progressBar').style.width   = pct + '%';
    document.getElementById('progressLabel').textContent = pct + '%';
  }

  // Load recent tasks (all, show first 5)
  const tasks = await fetch('api/tasks.php').then(r => r.json());
  const taskList = document.getElementById('recentTasksList');
  if (tasks.success && tasks.data.length > 0) {
    const recent = tasks.data.slice(0, 5);
    taskList.innerHTML = recent.map(t => `
      <div class="task-item ${t.status === 'completed' ? 'completed' : ''}">
        <div class="flex-grow-1">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="task-title">${escHtml(t.title)}</span>
            ${priorityBadge(t.priority)}
            ${statusBadge(t.status)}
          </div>
          <div class="task-meta d-flex gap-3 mt-1">
            ${t.subject_name ? `<span>${subjectPill(t.subject_name, t.subject_color)}</span>` : ''}
            ${t.due_date ? `<span><i class="bi bi-calendar3 me-1"></i>${formatDate(t.due_date)}</span>` : ''}
          </div>
        </div>
      </div>
    `).join('');
  } else {
    taskList.innerHTML = `<div class="empty-state"><i class="bi bi-check2-square"></i><p>No tasks yet.<br><a href="addtask.php">Add your first task</a></p></div>`;
  }

  // Load today's schedule
  const sched = await fetch('api/schedule.php').then(r => r.json());
  const schedList = document.getElementById('todayScheduleList');
  if (sched.success) {
    const todaySched = sched.data.filter(s => s.day_of_week === TODAY_NAME);
    if (todaySched.length > 0) {
      schedList.innerHTML = todaySched.map(s => `
        <div class="schedule-entry" style="border-left-color:${s.subject_color || '#4f46e5'}">
          <div class="schedule-time">${formatTime(s.start_time)}<br>${formatTime(s.end_time)}</div>
          <div>
            <div class="schedule-title">${escHtml(s.title)}</div>
            <div class="schedule-subject">${s.subject_name ? subjectPill(s.subject_name, s.subject_color) : 'No subject'}</div>
          </div>
        </div>
      `).join('');
    } else {
      schedList.innerHTML = `<div class="empty-state"><i class="bi bi-calendar-x"></i><p>No sessions scheduled for today.<br><a href="schedule.php">Add a session</a></p></div>`;
    }
  }
}

function priorityBadge(p) {
  return `<span class="badge-priority-${p}">${p.charAt(0).toUpperCase()+p.slice(1)}</span>`;
}
function statusBadge(s) {
  const labels = { pending: 'Pending', in_progress: 'In Progress', completed: 'Completed' };
  return `<span class="badge-status-${s}">${labels[s]||s}</span>`;
}
function subjectPill(name, color) {
  return `<span class="subject-dot" style="color:${color||'#4f46e5'}">${escHtml(name)}</span>`;
}
function formatDate(str) {
  if (!str) return '—';
  const [y,m,d] = str.split('-');
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${months[parseInt(m,10)-1]} ${parseInt(d,10)}`;
}
function formatTime(t) {
  if (!t) return '';
  const [h,m] = t.split(':');
  const hr = parseInt(h,10);
  return `${hr%12||12}:${m} ${hr>=12?'PM':'AM'}`;
}
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadDashboard();
</script>
