<?php
$pageTitle  = 'Tasks';
$activePage = 'tasks';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Tasks</h1>
    <p class="page-subtitle">Add, track, and complete your assignments</p>
  </div>
</div>

<div class="row g-4">
  <!-- Add Task Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add New Task
      </div>
      <div class="card-body">
        <div id="formAlert" class="alert d-none mb-3 py-2 px-3" style="font-size:.85rem;border-radius:9px"></div>
        <form id="addTaskForm">
          <div class="mb-3">
            <label class="form-label">Task Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="taskTitle" placeholder="e.g. Chapter 5 Notes" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="taskDesc" rows="2" placeholder="Optional details…"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Subject</label>
            <select class="form-select" id="taskSubject">
              <option value="">— No subject —</option>
            </select>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" id="taskDue">
            </div>
            <div class="col-5">
              <label class="form-label">Priority</label>
              <select class="form-select" id="taskPriority">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
              </select>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="addTaskBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Task
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Task List -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <span><i class="bi bi-list-task me-2 text-primary"></i>All Tasks</span>
          <!-- Filter Tabs -->
          <div class="d-flex gap-1 flex-wrap" id="filterTabs">
            <button class="btn btn-sm btn-primary active" data-filter="" style="border-radius:7px;font-size:.78rem">All</button>
            <button class="btn btn-sm btn-outline-secondary" data-filter="pending" style="border-radius:7px;font-size:.78rem">Pending</button>
            <button class="btn btn-sm btn-outline-secondary" data-filter="in_progress" style="border-radius:7px;font-size:.78rem">In Progress</button>
            <button class="btn btn-sm btn-outline-secondary" data-filter="completed" style="border-radius:7px;font-size:.78rem">Completed</button>
          </div>
        </div>
      </div>
      <div class="card-body" id="tasksList">
        <div class="text-center py-4 text-muted">
          <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
let currentFilter = '';

// Load subjects into dropdown
async function loadSubjectOptions() {
  const res  = await fetch('api/subjects.php');
  const data = await res.json();
  const sel  = document.getElementById('taskSubject');
  if (data.success && data.data) {
    data.data.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.textContent = s.name;
      sel.appendChild(opt);
    });
  }
}

async function loadTasks(filter = '') {
  const url  = filter ? `api/tasks.php?status=${filter}` : 'api/tasks.php';
  const res  = await fetch(url);
  const data = await res.json();
  const list = document.getElementById('tasksList');

  if (!data.success || !data.data.length) {
    list.innerHTML = `<div class="empty-state"><i class="bi bi-check2-square"></i><p>No ${filter || ''} tasks yet.</p></div>`;
    return;
  }

  list.innerHTML = data.data.map(t => {
    const overdue = t.status !== 'completed' && isPastDue(t.due_date);
    return `
    <div class="task-item ${t.status === 'completed' ? 'completed' : ''}" id="task_${t.id}">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="task-title">${escHtml(t.title)}</span>
            ${priorityBadge(t.priority)}
            ${statusBadge(t.status)}
          </div>
          <div class="d-flex gap-1">
            <select class="form-select form-select-sm status-select" data-id="${t.id}"
                    style="width:auto;font-size:.75rem;border-radius:7px;padding:.2rem .5rem">
              <option value="pending"     ${t.status==='pending'     ?'selected':''}>Pending</option>
              <option value="in_progress" ${t.status==='in_progress' ?'selected':''}>In Progress</option>
              <option value="completed"   ${t.status==='completed'   ?'selected':''}>Completed</option>
            </select>
            <button class="btn btn-sm text-danger p-1" style="background:none;border:none;font-size:.95rem"
                    onclick="deleteTask(${t.id})"><i class="bi bi-trash3"></i></button>
          </div>
        </div>
        ${t.description ? `<p class="task-meta mt-1 mb-0">${escHtml(t.description)}</p>` : ''}
        <div class="task-meta d-flex flex-wrap gap-3 mt-1">
          ${t.subject_name ? `<span>${subjectPill(t.subject_name, t.subject_color)}</span>` : ''}
          ${t.due_date ? `<span class="${overdue?'text-danger fw-600':''}"><i class="bi bi-calendar3 me-1"></i>${formatDate(t.due_date)}${overdue?' (Overdue)':''}</span>` : ''}
        </div>
      </div>
    </div>`;
  }).join('');

  // Bind status selects
  document.querySelectorAll('.status-select').forEach(sel => {
    sel.addEventListener('change', async function() {
      const res  = await fetch(`api/tasks.php?id=${this.dataset.id}`, {
        method: 'PATCH', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ status: this.value }),
      });
      const data = await res.json();
      if (data.success) { showToast('Status updated.', 'success'); loadTasks(currentFilter); }
      else showToast(data.message, 'danger');
    });
  });
}

async function deleteTask(id) {
  if (!confirm('Delete this task?')) return;
  const res  = await fetch(`api/tasks.php?id=${id}`, { method: 'DELETE' });
  const data = await res.json();
  if (data.success) { showToast('Task deleted.', 'success'); loadTasks(currentFilter); }
  else showToast(data.message, 'danger');
}

document.getElementById('filterTabs').addEventListener('click', (e) => {
  const btn = e.target.closest('[data-filter]');
  if (!btn) return;
  document.querySelectorAll('#filterTabs button').forEach(b => {
    b.className = 'btn btn-sm btn-outline-secondary';
    b.style.borderRadius = '7px'; b.style.fontSize = '.78rem';
  });
  btn.className = 'btn btn-sm btn-primary active';
  btn.style.borderRadius = '7px'; btn.style.fontSize = '.78rem';
  currentFilter = btn.dataset.filter;
  loadTasks(currentFilter);
});

document.getElementById('addTaskForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn   = document.getElementById('addTaskBtn');
  const title = document.getElementById('taskTitle').value.trim();
  if (!title) { showFormAlert('Task title is required.', 'danger'); return; }

  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding…';
  btn.disabled  = true;

  const payload = {
    title,
    description: document.getElementById('taskDesc').value.trim(),
    subject_id:  document.getElementById('taskSubject').value || null,
    due_date:    document.getElementById('taskDue').value     || null,
    priority:    document.getElementById('taskPriority').value,
  };

  const res  = await fetch('api/tasks.php', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify(payload),
  });
  const data = await res.json();

  if (data.success) {
    showToast('Task added!', 'success');
    e.target.reset();
    loadTasks(currentFilter);
  } else {
    showFormAlert(data.message, 'danger');
  }
  btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add Task';
  btn.disabled  = false;
});

function showFormAlert(msg, type) {
  const a = document.getElementById('formAlert');
  a.className = `alert alert-${type} mb-3 py-2 px-3`;
  a.textContent = msg;
  a.classList.remove('d-none');
  setTimeout(() => a.classList.add('d-none'), 4000);
}

function priorityBadge(p) { return `<span class="badge-priority-${p}">${p.charAt(0).toUpperCase()+p.slice(1)}</span>`; }
function statusBadge(s) {
  const l = {pending:'Pending',in_progress:'In Progress',completed:'Completed'};
  return `<span class="badge-status-${s}">${l[s]||s}</span>`;
}
function subjectPill(name, color) { return `<span class="subject-dot" style="color:${color||'#4f46e5'}">${escHtml(name)}</span>`; }
function formatDate(str) {
  if (!str) return '—';
  const [y,m,d] = str.split('-');
  const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  return `${months[parseInt(m,10)-1]} ${parseInt(d,10)}, ${y}`;
}
function isPastDue(str) {
  if (!str) return false;
  const today = new Date(); today.setHours(0,0,0,0);
  return new Date(str+'T00:00:00') < today;
}
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadSubjectOptions();
loadTasks();
</script>
