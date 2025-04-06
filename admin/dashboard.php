<?php 
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
    include '../db.php';

    // Count new orders with pending status
    $sqlCount = "SELECT COUNT(DISTINCT order_id) AS newOrderCount FROM orders WHERE order_status = 'Pending'";
    $resultCount = mysqli_query($conn, $sqlCount);
    $rowCount = mysqli_fetch_assoc($resultCount);
    $newOrders = $rowCount['newOrderCount'];


    // Calculate total money earned (sum of total_price for orders with status 'Shipped')
    $sqlMoneyEarned = "SELECT SUM(total_price) AS totalMoneyEarned FROM orders WHERE order_status = 'Delivered'";
    $resultMoneyEarned = mysqli_query($conn, $sqlMoneyEarned);
    $rowMoneyEarned = mysqli_fetch_assoc($resultMoneyEarned);
    $totalMoneyEarned = $rowMoneyEarned['totalMoneyEarned'] ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #1977F3;
      --primary-light: #e0e7ff;
      --secondary-color: #3f37c9;
      --text-dark: #1e293b;
      --text-light: #64748b;
      --white: #ffffff;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8fafc;
      color: var(--text-dark);
      line-height: 1.6;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: var(--white);
      padding: 1.5rem 0;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
    }
    
    header .container {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    header h1 {
      font-size: 1.8rem;
      font-weight: 600;
    }
    
    .admin-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .admin-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: var(--primary-light);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .dashboard {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }
    
    .card {
      background: var(--white);
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
    }
    
    .card-title {
      font-size: 1.1rem;
      font-weight: 500;
      color: var(--text-light);
    }
    
    .card-icon {
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--white);
    }
    
    .card-icon.primary {
      background-color: var(--primary-color);
    }
    
    .card-icon.success {
      background-color: var(--success-color);
    }
    
    .card-value {
      font-size: 2rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }
    
    .card-footer {
      font-size: 0.9rem;
      color: var(--text-light);
    }
    
    .actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    
    .action-btn {
      background: var(--white);
      border: none;
      border-radius: 8px;
      padding: 1.5rem 1rem;
      text-align: center;
      color: var(--primary-color);
      font-weight: 500;
      font-size: 1rem;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }
    
    .action-btn:hover {
      background: var(--primary-light);
      transform: translateY(-3px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
    }
    
    .action-icon {
      font-size: 1.5rem;
    }
    
    .badge {
      background-color: var(--danger-color);
      color: var(--white);
      padding: 0.25rem 0.5rem;
      border-radius: 999px;
      font-size: 0.75rem;
      font-weight: 600;
      margin-left: 0.5rem;
    }
    
    @media (max-width: 768px) {
      header .container {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
      }
      
      .dashboard {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
      <h1>Admin Dashboard</h1>
      <div class="admin-info">
        <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?></div>
        <span><?php echo $_SESSION['admin_name']; ?></span>
      </div>
    </div>
  </header>
  
  <main class="container">
    <section class="dashboard">
      <div class="card">
        <div class="card-header">
          <span class="card-title">Pending Orders</span>
          <div class="card-icon primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
          </div>
        </div>
        <div class="card-value"><?php echo $newOrders; ?></div>
        <div class="card-footer">New orders awaiting processing</div>
      </div>
      
      <div class="card">
        <div class="card-header">
          <span class="card-title">Total Revenue</span>
          <div class="card-icon success">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1" x2="12" y2="23"></line>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </div>
        </div>
        <div class="card-value">Rs <?php echo number_format($totalMoneyEarned, 2); ?></div>
        <div class="card-footer">From delivered orders</div>
      </div>
    </section>
    
    <section class="actions">
      <a href="add_medicine.php" class="action-btn">
        <div class="action-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a10 10 0 1 0 10 10 4 4 0 0 1-5-5 4 4 0 0 1-5-5"></path>
            <path d="M8.5 8.5v.01"></path>
            <path d="M16 15.5v.01"></path>
            <path d="M12 12v.01"></path>
            <path d="M11 17v.01"></path>
            <path d="M7 14v.01"></path>
            <path d="M17 14v.01"></path>
            <path d="M15 11v.01"></path>
            <path d="M9 11v.01"></path>
          </svg>
        </div>
        Add Medicine
      </a>
      
      <a href="view_medicines.php" class="action-btn">
        <div class="action-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </div>
        View Medicines
      </a>
      
      <a href="order.php" class="action-btn">
        <div class="action-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
        </div>
        All Orders
        <?php if ($newOrders > 0): ?>
          <span class="badge"><?php echo $newOrders; ?></span>
        <?php endif; ?>
      </a>
    </section>
  </main>
</body>
</html>