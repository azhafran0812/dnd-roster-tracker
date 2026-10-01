<?php
require 'config.php';
$isLoggedIn = isset($_SESSION['idToken']);
$characters = [];

if ($isLoggedIn) {
    $uid = $_SESSION['localId'];
    
    $url = FIREBASE_DB_URL . "characters/" . $uid . ".json?auth=" . $_SESSION['idToken'];
    $response = firebase_request($url, 'GET');

    if (is_array($response) && !isset($response['error'])) {
        $characters = $response;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D&D Character Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        body { background-color: #121212; background-image: radial-gradient(circle, #1a1a1a 0%, #0a0a0a 100%); color: #d4af37; font-family: 'Crimson Text', serif; min-height: 100vh; }
        h1, h2, h3, h4, h5, .cinzel-font { font-family: 'Cinzel', serif; color: #d4af37; text-shadow: 2px 2px 4px rgba(0,0,0,0.8); }
        .card { background-color: #1e1e1e; border: 1px solid #4a0404; box-shadow: 0 4px 8px rgba(0,0,0,0.5); }
        .card-header { background-color: #4a0404 !important; border-bottom: 2px solid #d4af37; }
        .form-control, .form-select { background-color: #2c2c2c; border: 1px solid #555; color: #f0e6d2; font-family: 'Crimson Text', serif; }
        .form-control:focus, .form-select:focus { background-color: #333; border-color: #d4af37; box-shadow: 0 0 5px rgba(212, 175, 55, 0.5); color: #f0e6d2; }
        .form-control::placeholder { color: #777; font-style: italic; }
        .table { color: #f0e6d2; border-color: #444; }
        .table-dark th { background-color: #4a0404 !important; border-bottom: 2px solid #d4af37; font-family: 'Cinzel', serif; letter-spacing: 1px; color: #d4af37; }
        .table-hover tbody tr:hover { background-color: #2a2a2a; color: #d4af37; }
        .btn-primary { background-color: #4a0404; border: 1px solid #d4af37; color: #d4af37; font-family: 'Cinzel', serif; transition: all 0.3s; }
        .btn-primary:hover { background-color: #6b0505; border-color: #f1c40f; color: #fff; }
        .btn-warning { background-color: #b8860b; border: 1px solid #d4af37; color: #121212; font-weight: bold; }
        .badge.bg-success { background-color: #1e5631 !important; border: 1px solid #4c9a2a; }
        .badge.bg-danger { background-color: #7a0000 !important; border: 1px solid #ff4c4c; }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
<!-- SESI AUTENTIKASI -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h2 class="text-center mb-4 cinzel-font fs-1">⚔️ Enter the Realm ⚔️</h2>
            <div class="card">
                <div class="card-header text-center"><h5 class="mb-0 cinzel-font">Identify Yourself</h5></div>
                <div class="card-body">
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="text-center mb-3 text-danger fw-bold"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="text-center mb-3 text-success fw-bold"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
                    <?php endif; ?>

                    <form action="auth.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label text-warning">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="adventurer@guild.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Secret Password</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" name="action" value="login" class="btn btn-primary w-100 mb-2">Login</button>
                        <button type="submit" name="action" value="register" class="btn btn-warning w-100">Register New Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- CRUD nya -->
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 cinzel-font fs-1">⚔️ D&D Character Roster ⚔️</h2>
        <a href="auth.php?action=logout" class="btn btn-danger" style="background-color: #8b0000; border-color: #4a0404; font-family: 'Cinzel', serif;">Leave Realm (Logout)</a>
    </div>
    
    <div class="row">
        <!-- Form Create & Update -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header text-center"><h5 class="mb-0 cinzel-font" id="formTitle">Add Character</h5></div>
                <div class="card-body">
                    <form action="crud.php" method="POST" id="characterForm">
                        <input type="hidden" name="id" id="charId">
                        <div class="mb-3">
                            <label class="form-label text-warning">Character Name</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Example: Krakatoa Abysswhirl or Bryn" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Race</label>
                            <input type="text" name="race" id="race" class="form-control" placeholder="Example: Locathah" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Class</label>
                            <input type="text" name="charClass" id="charClass" class="form-control" placeholder="Example: Artificer" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Level</label>
                            <input type="number" name="level" id="level" class="form-control" min="1" max="20" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-warning">Status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="Active">Active (On-going Campaign)</option>
                                <option value="Retired">Retired / Deceased</option>
                            </select>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-primary w-100 mt-2">Scribe to Roster</button>
                        <button type="button" id="cancelBtn" class="btn btn-secondary w-100 mt-2 d-none" onclick="resetForm()">Abandon Edit</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th><th>Race</th><th>Class</th><th>Level</th><th>Status</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($characters)): ?>
                                    <tr><td colspan="6" class="text-center text-muted" style="font-style: italic;">The roster is empty. Await the arrival of new adventurers...</td></tr>
                                <?php else: ?>
                                    
                                    <?php foreach ($characters as $firebaseId => $char): 
                                        
                                        if(!isset($char['name'])) continue; 
                                        $badgeClass = $char['status'] === 'Active' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <tr>
                                        <td class="fw-bold" style="color: #d4af37;"><?= htmlspecialchars($char['name']) ?></td>
                                        <td><?= htmlspecialchars($char['race']) ?></td>
                                        <td><?= htmlspecialchars($char['charClass']) ?></td>
                                        <td><?= htmlspecialchars($char['level']) ?></td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($char['status']) ?></span></td>
                                        <td>
                                            
                                            <button class="btn btn-sm btn-warning mb-1" 
                                                onclick="editChar('<?= $firebaseId ?>', '<?= addslashes(htmlspecialchars($char['name'])) ?>', '<?= addslashes(htmlspecialchars($char['race'])) ?>', '<?= addslashes(htmlspecialchars($char['charClass'])) ?>', <?= $char['level'] ?>, '<?= $char['status'] ?>')">
                                                Edit
                                            </button>
                                            <a href="crud.php?action=delete&id=<?= $firebaseId ?>" class="btn btn-sm btn-danger mb-1" style="background-color: #8b0000; border-color: #4a0404;" onclick="return confirm('Are you sure you want to banish this character from the roster?')">Delete</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

function editChar(id, name, race, charClass, level, status) {
    document.getElementById('charId').value = id;
    document.getElementById('name').value = name;
    document.getElementById('race').value = race;
    document.getElementById('charClass').value = charClass;
    document.getElementById('level').value = level;
    document.getElementById('status').value = status;
    
    document.getElementById('formTitle').innerText = "Edit Character";
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.innerText = "Update Scroll";
    saveBtn.classList.replace('btn-primary', 'btn-warning');
    document.getElementById('cancelBtn').classList.remove('d-none');
}

function resetForm() {
    document.getElementById('characterForm').reset();
    document.getElementById('charId').value = "";
    document.getElementById('formTitle').innerText = "Add Character";
    const saveBtn = document.getElementById('saveBtn');
    saveBtn.innerText = "Scribe to Roster";
    saveBtn.classList.replace('btn-warning', 'btn-primary');
    document.getElementById('cancelBtn').classList.add('d-none');
}
</script>
<?php endif; ?>

</body>
</html>