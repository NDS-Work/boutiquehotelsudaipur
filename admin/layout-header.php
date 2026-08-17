<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' — ' : ''; ?>Admin</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Serif+Display&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --bg:       #0c0e0d;
    --surface:  #141614;
    --surface2: #1a1e1a;
    --border:   #242824;
    --accent:   #9ebffe;
    --accent2:  #f5c835;
    --text:     #c8d0c8;
    --muted:    #556055;
    --danger:   #e06363;
    --success:  #63e09a;
}
body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Mono', monospace;
    font-size: 13px;
    min-height: 100vh;
    display: flex;
}

/* Sidebar */
.sidebar {
    width: 220px;
    flex-shrink: 0;
    background: var(--surface);
    border-right: 1px solid var(--border);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 100;
}
.sidebar-logo {
    padding: 24px 20px;
    border-bottom: 1px solid var(--border);
}
.sidebar-logo span {
    font-family: 'DM Serif Display', serif;
    color: var(--accent);
    font-size: 1rem;
    display: block;
    line-height: 1.3;
}
.sidebar-logo small { color: var(--muted); font-size: 10px; }
.sidebar-nav { padding: 16px 0; flex: 1; }
.nav-label {
    padding: 6px 20px;
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--muted);
    margin-top: 8px;
}
.nav-item a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 9px 20px;
    color: var(--muted);
    text-decoration: none;
    font-size: 12px;
    transition: all 0.15s;
    border-left: 2px solid transparent;
}
.nav-item a:hover { color: var(--text); background: var(--surface2); }
.nav-item a.active { color: var(--accent); border-left-color: var(--accent); background: var(--surface2); }
.nav-item a .icon { width: 16px; text-align: center; font-size: 14px; }
.sidebar-footer {
    padding: 16px 20px;
    border-top: 1px solid var(--border);
}
.sidebar-footer a {
    color: var(--muted);
    text-decoration: none;
    font-size: 11px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.15s;
}
.sidebar-footer a:hover { color: var(--danger); }

/* Main */
.main {
    margin-left: 220px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.topbar {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.topbar h1 {
    font-family: 'DM Serif Display', serif;
    font-size: 1.3rem;
    color: var(--text);
    font-weight: 400;
}
.topbar-actions { display: flex; gap: 10px; align-items: center; }
.content { padding: 28px; flex: 1; }

/* Cards */
.card {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 20px 24px;
    margin-bottom: 24px;
}
.card-title {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}

/* Stat cards */
.stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
.stat {
    background: var(--surface);
    border: 1px solid var(--border);
    padding: 20px;
}
.stat-value { font-family: 'DM Serif Display', serif; font-size: 2rem; color: var(--accent); line-height: 1; margin-bottom: 4px; }
.stat-label { color: var(--muted); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }

/* Table */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
th {
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--muted);
    padding: 10px 14px;
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
td {
    padding: 12px 14px;
    border-bottom: 1px solid #1a1e1a;
    font-size: 12px;
    vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: var(--surface2); }
.venue-thumb { width: 48px; height: 36px; object-fit: cover; }
.badge {
    display: inline-block;
    padding: 2px 8px;
    font-size: 10px;
    border-radius: 2px;
    font-weight: 500;
}
.badge-green  { background: #0e2a1a; color: var(--success); }
.badge-yellow { background: #2a220e; color: var(--accent2); }
.badge-gray   { background: #1a1e1a; color: var(--muted); }

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.15s;
}
.btn-primary { background: var(--accent); color: #0c0e0d; }
.btn-primary:hover { background: #d9fb06; }
.btn-secondary { background: var(--surface2); color: var(--text); border: 1px solid var(--border); }
.btn-secondary:hover { border-color: var(--accent); color: var(--accent); }
.btn-danger { background: #2a1414; color: var(--danger); border: 1px solid #5a2020; }
.btn-danger:hover { background: #3a1818; }
.btn-sm { padding: 5px 10px; font-size: 11px; }

/* Forms */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-group { margin-bottom: 20px; }
.form-group.full { grid-column: 1 / -1; }
label { display: block; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
input[type=text], input[type=number], input[type=url], textarea, select {
    width: 100%;
    background: #0c0e0d;
    border: 1px solid var(--border);
    color: var(--text);
    padding: 10px 14px;
    font-family: 'DM Mono', monospace;
    font-size: 12px;
    outline: none;
    transition: border-color 0.15s;
}
input:focus, textarea:focus, select:focus { border-color: var(--accent); }
textarea { resize: vertical; min-height: 100px; }
select option { background: #0c0e0d; }
.form-hint { color: var(--muted); font-size: 10px; margin-top: 4px; }
.checkbox-label { display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--text); text-transform: none; letter-spacing: 0; font-size: 12px; }
input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--accent); }

/* Alert */
.alert { padding: 12px 16px; font-size: 12px; margin-bottom: 20px; border-left: 3px solid; }
.alert-success { background: #0e2a1a; border-color: var(--success); color: var(--success); }
.alert-error   { background: #2a1414; border-color: var(--danger); color: var(--danger); }

/* Image preview grid */
.image-preview-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
.image-preview-item { position: relative; }
.image-preview-item img { width: 80px; height: 60px; object-fit: cover; display: block; }
.image-preview-item .remove-img {
    position: absolute; top: -6px; right: -6px;
    background: var(--danger); color: white;
    border: none; border-radius: 50%;
    width: 18px; height: 18px;
    font-size: 10px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.upload-area {
    border: 1px dashed var(--border);
    padding: 24px;
    text-align: center;
    color: var(--muted);
    font-size: 12px;
    cursor: pointer;
    transition: border-color 0.15s;
}
.upload-area:hover { border-color: var(--accent); color: var(--accent); }
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-logo">
        <span>Venues Admin</span>
        <small>boutiquehotelsudaipur.com</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <div class="nav-item">
            <a href="/admin/index.php" class="<?php echo ($currentPage ?? '') === 'dashboard' ? 'active' : ''; ?>">
                <span class="icon">⊞</span> Dashboard
            </a>
        </div>
        <div class="nav-label">Content</div>
        <div class="nav-item">
            <a href="/admin/venues.php" class="<?php echo ($currentPage ?? '') === 'venues' ? 'active' : ''; ?>">
                <span class="icon">◈</span> All Venues
            </a>
        </div>
        <div class="nav-item">
            <a href="/admin/venue-add.php" class="<?php echo ($currentPage ?? '') === 'venue-add' ? 'active' : ''; ?>">
                <span class="icon">+</span> Add Venue
            </a>
        </div>
        <div class="nav-label">Inquiries</div>
        <div class="nav-item">
            <a href="/admin/inquiries.php" class="<?php echo ($currentPage ?? '') === 'inquiries' ? 'active' : ''; ?>">
                <span class="icon">✉</span> Contact Inquiries
            </a>
        </div>
        <div class="nav-item">
            <a href="/admin/booking.php" class="<?php echo ($currentPage ?? '') === 'booking' ? 'active' : ''; ?>">
                <span class="icon">></span> Bookings
            </a>
        </div>
        <div class="nav-label">Site</div>
        <div class="nav-item">
            <a href="/" target="_blank">
                <span class="icon">↗</span> View Site
            </a>
        </div>
        <div class="nav-label">Assets</div>
        <div class="nav-item">
            <a href="/admin/amenities.php" class="<?php echo ($currentPage ?? '') === 'amenities' ? 'active' : ''; ?>">
                <span class="icon">></span> Amenities 
            </a>
        </div>
        <div class="nav-item">
            <a href="/admin/attraction.php" class="<?php echo ($currentPage ?? '') === 'attraction' ? 'active' : ''; ?>">
                <span class="icon">></span> Attractions 
            </a>
        </div>
        <div class="nav-item">
            <a href="/admin/collection.php" class="<?php echo ($currentPage ?? '') === 'collection' ? 'active' : ''; ?>">
                <span class="icon">></span> Collections
            </a>
        </div>
        <div class="nav-item">
            <a href="/admin/occasion.php" class="<?php echo ($currentPage ?? '') === 'occasion' ? 'active' : ''; ?>">
                <span class="icon">></span> Occasions
            </a>
        </div>
        
        <div class="nav-label">Analytics</div>
        <div class="nav-item">
            <a href="/admin/visitors.php" class="<?php echo ($currentPage ?? '') === 'visitors' ? 'active' : ''; ?>">
                <span class="icon">></span> Visitor
            </a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <a href="/admin/logout.php">✕ &nbsp;Logout</a>
    </div>
</div>

        


<div class="main">