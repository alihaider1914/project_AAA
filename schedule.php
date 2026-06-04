<?php
$pageTitle  = 'Study Schedule';
$activePage = 'schedule';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Study Schedule</h1>
    <p class="page-subtitle">Plan your weekly study timetable</p>
  </div>
</div>

<div class="row g-4">
  <!-- Add Schedule Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-calendar-plus-fill me-2 text-primary"></i>Add Session
      </div>
      <div class="card-body">
        <div id="formAlert" class="alert d-none mb-3 py-2 px-3" style="font-size:.85rem;border-radius:9px"></div>
        <form id="addSchedForm">
          <div class="mb-3">
            <label class="form-label">Session Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="schedTitle" placeholder="e.g. Math Study Session" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <select class="form-select" id="schedSubject">
              <option value="">— No subject —</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Day of Week <span class="text-danger">*</span></label>
            <select class="form-select" id="schedDay" required>
              <option value="">— Select day —</option>
              <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                <option><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label">Start Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="schedStart" required>
            </div>
            <div class="col-6">
              <label class="form-label">End Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control" id="schedEnd" required>
            </div>
          </div>
          <div class="mb-4">
            <label class="form-label">Notes</label>
            <textarea class="form-control" id="schedNotes" rows="2" placeholder="Optional notes…"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="addSchedBtn">
            <i class="bi bi-plus-lg me-1"></i> Add to Schedule
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Weekly Schedule View -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-calendar-week-fill me-2 text-warning"></i>Weekly Timetable</span>
        <span class="badge bg-primary rounded-pill" id="schedCount">0</span>
      </div>
      <div class="card-body" id="scheduleView">
        <div class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

async function loadSubjectOptions() {
  const res  = await fetch('api/subjects.php');
  const data = await res.json();
  const sel  = document.getElementById('schedSubject');
  if (data.success && data.data) {
    data.data.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id; opt.textContent = s.name; sel.appendChild(opt);
    });
  }
}

async function loadSchedule() {
  const res  = await fetch('api/schedule.php');
  const data = await res.json();
  const view = document.getElementById('scheduleView');

  if (!data.success || !data.data.length) {
    view.innerHTML = `<div class="empty-state"><i class="bi bi-calendar-x"></i><p>No sessions scheduled yet.<br>Use the form to add your first study session.</p></div>`;
    document.getElementById('schedCount').textContent = 0;
    return;
  }

  document.getElementById('schedCount').textContent = data.data.length;

  // Group by day
  const byDay = {};
  DAYS.forEach(d => byDay[d] = []);
  data.data.forEach(s => { if (byDay[s.day_of_week]) byDay[s.day_of_week].push(s); });

  const today = DAYS[new Date().getDay() === 0 ? 6 : new Date().getDay() - 1]; // Mon=0…Sun=6

  let html = '';
  DAYS.forEach(day => {
    if (!byDay[day].length) return;
    const isToday = day === today;
    html += `
      <div class="schedule-day-group">
        <div class="schedule-day-title d-flex align-items-center gap-2">
          ${day}
          ${isToday ? '<span class="badge bg-primary" style="font-size:.65rem">Today</span>' : ''}
        </div>
        ${byDay[day].map(s => `
          <div class="schedule-entry" style="border-left-color:${s.subject_color||'#4f46e5'}">
            <div class="schedule-time">
              ${fmtTime(s.start_time)}<br>
              <span style="color:#94a3b8;font-weight:500">${fmtTime(s.end_time)}</span>
            </div>
            <div class="flex-grow-1">
              <div class="schedule-title">${escHtml(s.title)}</div>
              <div class="schedule-subject">
                ${s.subject_name ? subjectPill(s.subject_name, s.subject_color) : '<span class="text-muted" style="font-size:.75rem">No subject</span>'}
                ${s.notes ? `<span class="text-muted ms-2" style="font-size:.75rem"><i class="bi bi-sticky me-1"></i>${escHtml(s.notes)}</span>` : ''}
              </div>
            </div>
            <button class="btn p-0 text-danger" style="background:none;border:none;font-size:.95rem;align-self:flex-start"
                    onclick="deleteEntry(${s.id})"><i class="bi bi-x-lg"></i></button>
          </div>
        `).join('')}
      </div>`;
  });

  view.innerHTML = html || `<div class="empty-state"><i class="bi bi-calendar-x"></i><p>No sessions yet.</p></div>`;
}

async function deleteEntry(id) {
  if (!confirm('Remove this schedule entry?')) return;
  const res  = await fetch(`api/schedule.php?id=${id}`, { method: 'DELETE' });
  const data = await res.json();
  if (data.success) { showToast('Entry removed.', 'success'); loadSchedule(); }
  else showToast(data.message, 'danger');
}

document.getElementById('addSchedForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn   = document.getElementById('addSchedBtn');
  const title = document.getElementById('schedTitle').value.trim();
  const day   = document.getElementById('schedDay').value;
  const start = document.getElementById('schedStart').value;
  const end   = document.getElementById('schedEnd').value;

  if (!title || !day || !start || !end) { showFormAlert('Please fill all required fields.', 'warning'); return; }
  if (start >= end) { showFormAlert('End time must be after start time.', 'danger'); return; }

  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
  btn.disabled  = true;

  const payload = {
    title,
    day_of_week: day,
    start_time:  start,
    end_time:    end,
    subject_id:  document.getElementById('schedSubject').value || null,
    notes:       document.getElementById('schedNotes').value.trim(),
  };

  const res  = await fetch('api/schedule.php', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify(payload),
  });
  const data = await res.json();

  if (data.success) {
    showToast('Session added to schedule!', 'success');
    e.target.reset();
    loadSchedule();
  } else {
    showFormAlert(data.message, 'danger');
  }
  btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add to Schedule';
  btn.disabled  = false;
});

function showFormAlert(msg, type) {
  const a = document.getElementById('formAlert');
  a.className = `alert alert-${type} mb-3 py-2 px-3`;
  a.textContent = msg;
  a.classList.remove('d-none');
  setTimeout(() => a.classList.add('d-none'), 4000);
}
function subjectPill(name, color) { return `<span class="subject-dot" style="color:${color||'#4f46e5'}">${escHtml(name)}</span>`; }
function fmtTime(t) {
  if (!t) return '';
  const [h,m] = t.split(':');
  const hr = parseInt(h,10);
  return `${hr%12||12}:${m} ${hr>=12?'PM':'AM'}`;
}
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadSubjectOptions();
loadSchedule();
</script>
