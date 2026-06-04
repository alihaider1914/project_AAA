<?php
$pageTitle  = 'Profile';
$activePage = 'profile';
require_once 'includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">View your stats and manage your account</p>
  </div>
</div>

<div class="row g-4">
  <!-- Profile Card -->
  <div class="col-lg-4">
    <div class="card text-center mb-4">
      <div class="card-body py-4">
        <div class="profile-avatar-lg mx-auto" id="profileAvatar"><?= $avatarLetter ?></div>
        <h4 class="fw-700 mb-0" id="profileName"><?= $currentUser ?></h4>
        <p class="text-muted mb-3" id="profileEmail" style="font-size:.875rem"><?= $currentEmail ?></p>
        <p class="text-muted mb-0" style="font-size:.8rem">
          <i class="bi bi-calendar3 me-1"></i>Joined <span id="profileJoined">—</span>
        </p>
      </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="card">
      <div class="card-header"><i class="bi bi-pencil-fill me-2 text-primary"></i>Edit Profile</div>
      <div class="card-body">
        <div id="profileAlert" class="alert d-none mb-3 py-2 px-3" style="font-size:.85rem;border-radius:9px"></div>
        <form id="profileForm">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" class="form-control" id="editName" value="<?= $currentUser ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" id="editPassword" placeholder="Leave blank to keep current">
          </div>
          <div class="mb-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="editPasswordConfirm" placeholder="Repeat new password">
          </div>
          <button type="submit" class="btn btn-primary w-100" id="saveProfileBtn">
            <i class="bi bi-save me-1"></i> Save Changes
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Stats Column -->
  <div class="col-lg-8">
    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="stat-card text-center">
          <div class="stat-icon indigo mx-auto mb-2"><i class="bi bi-journal-bookmark-fill"></i></div>
          <div class="stat-value" id="pSubjects">—</div>
          <div class="stat-label">Subjects</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card text-center">
          <div class="stat-icon cyan mx-auto mb-2"><i class="bi bi-list-task"></i></div>
          <div class="stat-value" id="pTasks">—</div>
          <div class="stat-label">Total Tasks</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card text-center">
          <div class="stat-icon green mx-auto mb-2"><i class="bi bi-check-circle-fill"></i></div>
          <div class="stat-value" id="pCompleted">—</div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card text-center">
          <div class="stat-icon amber mx-auto mb-2"><i class="bi bi-calendar-week-fill"></i></div>
          <div class="stat-value" id="pSchedule">—</div>
          <div class="stat-label">Sessions</div>
        </div>
      </div>
    </div>

    <!-- Completion Chart -->
    <div class="card">
      <div class="card-header"><i class="bi bi-bar-chart-fill me-2 text-success"></i>Task Completion Rate</div>
      <div class="card-body">
        <div class="d-flex justify-content-between mb-1">
          <span style="font-size:.85rem;font-weight:600">Progress</span>
          <span style="font-size:.85rem;font-weight:700;color:#4f46e5" id="completionPct">0%</span>
        </div>
        <div class="progress mb-4" style="height:12px;border-radius:999px;background:#e0e7ff">
          <div class="progress-bar" id="completionBar" role="progressbar"
               style="border-radius:999px;background:linear-gradient(90deg,#4f46e5,#818cf8);width:0%"></div>
        </div>

        <div class="row g-3">
          <div class="col-4 text-center">
            <div class="fw-800 fs-4" style="color:#64748b" id="cntPending">—</div>
            <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase">Pending</div>
          </div>
          <div class="col-4 text-center">
            <div class="fw-800 fs-4" style="color:#d97706" id="cntInProgress">—</div>
            <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase">In Progress</div>
          </div>
          <div class="col-4 text-center">
            <div class="fw-800 fs-4" style="color:#059669" id="cntCompleted">—</div>
            <div style="font-size:.75rem;color:#94a3b8;font-weight:600;text-transform:uppercase">Completed</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
async function loadProfile() {
  const res  = await fetch('api/profile.php');
  const data = await res.json();
  if (!data.success) return;
  const d = data.data;

  document.getElementById('profileJoined').textContent = new Date(d.created_at).toLocaleDateString('en-US',{year:'numeric',month:'long'});
  document.getElementById('pSubjects').textContent     = d.total_subjects;
  document.getElementById('pTasks').textContent        = d.total_tasks;
  document.getElementById('pCompleted').textContent    = d.completed_tasks;
  document.getElementById('pSchedule').textContent     = d.schedule_entries;

  const pct = d.total_tasks > 0 ? Math.round((d.completed_tasks / d.total_tasks) * 100) : 0;
  document.getElementById('completionBar').style.width = pct + '%';
  document.getElementById('completionPct').textContent = pct + '%';
}

async function loadTaskBreakdown() {
  const [pending, inProgress, completed] = await Promise.all([
    fetch('api/tasks.php?status=pending').then(r=>r.json()),
    fetch('api/tasks.php?status=in_progress').then(r=>r.json()),
    fetch('api/tasks.php?status=completed').then(r=>r.json()),
  ]);
  document.getElementById('cntPending').textContent    = pending.data?.length    ?? 0;
  document.getElementById('cntInProgress').textContent = inProgress.data?.length ?? 0;
  document.getElementById('cntCompleted').textContent  = completed.data?.length  ?? 0;
}

document.getElementById('profileForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn      = document.getElementById('saveProfileBtn');
  const name     = document.getElementById('editName').value.trim();
  const password = document.getElementById('editPassword').value;
  const confirm  = document.getElementById('editPasswordConfirm').value;

  if (!name) { showAlert('Name is required.', 'danger'); return; }
  if (password && password !== confirm) { showAlert('Passwords do not match.', 'danger'); return; }
  if (password && password.length < 6)  { showAlert('Password must be at least 6 characters.', 'warning'); return; }

  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
  btn.disabled  = true;

  const res  = await fetch('api/profile.php', {
    method: 'PUT', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({ name, new_password: password }),
  });
  const data = await res.json();

  if (data.success) {
    showToast('Profile updated!', 'success');
    document.getElementById('profileName').textContent = name;
    document.getElementById('profileAvatar').textContent = name.charAt(0).toUpperCase();
    document.getElementById('editPassword').value = '';
    document.getElementById('editPasswordConfirm').value = '';
  } else {
    showAlert(data.message, 'danger');
  }

  btn.innerHTML = '<i class="bi bi-save me-1"></i> Save Changes';
  btn.disabled  = false;
});

function showAlert(msg, type) {
  const a = document.getElementById('profileAlert');
  a.className = `alert alert-${type} mb-3 py-2 px-3`;
  a.textContent = msg;
  a.classList.remove('d-none');
  setTimeout(() => a.classList.add('d-none'), 4000);
}

loadProfile();
loadTaskBreakdown();
</script>
