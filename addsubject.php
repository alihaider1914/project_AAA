<?php
$pageTitle  = 'Subjects';
$activePage = 'subjects';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Subjects</h1>
    <p class="page-subtitle">Manage your courses and subjects</p>
  </div>
</div>

<div class="row g-4">
  <!-- Add Subject Form -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-plus-circle-fill me-2 text-primary"></i>Add New Subject
      </div>
      <div class="card-body">
        <div id="formAlert" class="alert d-none mb-3 py-2 px-3" style="font-size:.85rem;border-radius:9px"></div>
        <form id="addSubjectForm">
          <div class="mb-3">
            <label class="form-label">Subject Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="subjectName" placeholder="e.g. Mathematics" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="subjectDesc" rows="3" placeholder="Optional description…"></textarea>
          </div>
          <div class="mb-4">
            <label class="form-label">Color Tag</label>
            <input type="hidden" id="subjectColor" value="#4f46e5">
            <div class="color-swatches">
              <?php
              $colors = ['#4f46e5','#7c3aed','#db2777','#dc2626','#ea580c','#d97706','#16a34a','#0891b2','#0284c7','#64748b'];
              foreach ($colors as $c): ?>
                <div class="color-swatch <?= $c === '#4f46e5' ? 'selected' : '' ?>"
                     style="background:<?= $c ?>"
                     data-color="<?= $c ?>"
                     onclick="selectColor(this)"></div>
              <?php endforeach; ?>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" id="addSubjectBtn">
            <i class="bi bi-plus-lg me-1"></i> Add Subject
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Subjects List -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>My Subjects</span>
        <span class="badge bg-primary rounded-pill" id="subjectCount">0</span>
      </div>
      <div class="card-body">
        <div id="subjectsList">
          <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm" role="status"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
function selectColor(el) {
  document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('subjectColor').value = el.dataset.color;
}

async function loadSubjects() {
  const res  = await fetch('api/subjects.php');
  const data = await res.json();
  const list = document.getElementById('subjectsList');
  document.getElementById('subjectCount').textContent = data.data ? data.data.length : 0;

  if (!data.success || !data.data.length) {
    list.innerHTML = `<div class="empty-state"><i class="bi bi-journal-plus"></i><p>No subjects yet. Add your first one!</p></div>`;
    return;
  }

  list.innerHTML = `<div class="row g-3">${data.data.map(s => `
    <div class="col-sm-6" id="subject_${s.id}">
      <div class="subject-card" style="border-top-color:${s.color}">
        <div class="d-flex justify-content-between align-items-start">
          <div class="d-flex align-items-center gap-2">
            <div style="width:12px;height:12px;border-radius:4px;background:${s.color};flex-shrink:0"></div>
            <span class="subject-name">${escHtml(s.name)}</span>
          </div>
          <button class="btn btn-sm text-danger p-0 ms-2" style="background:none;border:none;font-size:1rem;line-height:1"
                  onclick="deleteSubject(${s.id}, '${escHtml(s.name)}')">
            <i class="bi bi-trash3"></i>
          </button>
        </div>
        ${s.description ? `<p class="subject-desc mt-2 mb-1">${escHtml(s.description)}</p>` : ''}
        <div class="task-count"><i class="bi bi-list-task me-1"></i>${s.task_count} task${s.task_count!=1?'s':''}</div>
      </div>
    </div>
  `).join('')}</div>`;
}

async function deleteSubject(id, name) {
  if (!confirm(`Delete "${name}"? Tasks linked to it will become unassigned.`)) return;
  const res = await fetch(`api/subjects.php?id=${id}`, { method: 'DELETE' });
  const data = await res.json();
  if (data.success) {
    document.getElementById(`subject_${id}`)?.remove();
    showAlert('Subject deleted.', 'success');
    const cnt = document.getElementById('subjectCount');
    cnt.textContent = parseInt(cnt.textContent) - 1;
    showToast('Subject deleted.', 'success');
  } else {
    showToast(data.message, 'danger');
  }
}

document.getElementById('addSubjectForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn  = document.getElementById('addSubjectBtn');
  const name = document.getElementById('subjectName').value.trim();
  const desc = document.getElementById('subjectDesc').value.trim();
  const color = document.getElementById('subjectColor').value;

  if (!name) { showAlert('Subject name is required.', 'danger'); return; }

  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding…';
  btn.disabled = true;

  const res  = await fetch('api/subjects.php', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ name, description: desc, color }),
  });
  const data = await res.json();

  if (data.success) {
    showToast('Subject added!', 'success');
    e.target.reset();
    document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
    document.querySelector('.color-swatch').classList.add('selected');
    document.getElementById('subjectColor').value = '#4f46e5';
    loadSubjects();
  } else {
    showAlert(data.message, 'danger');
  }
  btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> Add Subject';
  btn.disabled = false;
});

function showAlert(msg, type) {
  const a = document.getElementById('formAlert');
  a.className = `alert alert-${type} mb-3 py-2 px-3`;
  a.innerHTML = msg;
  a.classList.remove('d-none');
  setTimeout(() => a.classList.add('d-none'), 4000);
}
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

loadSubjects();
</script>
