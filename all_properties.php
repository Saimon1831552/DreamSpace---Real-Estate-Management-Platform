<?php
require_once 'db.php';

// Pagination settings
$limit = 3;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
$page = max($page, 1);
$offset = ($page - 1) * $limit;

// Total count (safe query with JOIN)
$count_sql = "SELECT COUNT(DISTINCT p.id) 
              FROM properties p 
              INNER JOIN property_images pi ON pi.property_id = p.id";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute();
$count_stmt->bind_result($total_rows);
$count_stmt->fetch();
$count_stmt->close();

$total_pages = ceil($total_rows / $limit);

// Fetch paginated property data with one image per property
$sql = "SELECT p.*, MIN(pi.image_path) AS image 
        FROM properties p 
        INNER JOIN property_images pi ON pi.property_id = p.id 
        GROUP BY p.id 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Properties</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .property-img {
      width: 100%;
      height: 220px;
      object-fit: cover;
      border-top-left-radius: .5rem;
      border-top-right-radius: .5rem;
    }
    .property-card {
      border-radius: .5rem;
      overflow: hidden;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .property-card:hover {
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
      transform: translateY(-3px);
    }
    .property-info {
      padding: 1rem;
    }
  </style>
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm px-4">
    <div class="container-fluid">
      <span class="navbar-brand fw-bold">Agent Dashboard</span>
      <div class="d-flex">
        <a href="agent_dashboard.php" class="nav-link text-white">Home</a>
        <a href="logout.php" class="nav-link text-white ms-3">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container py-4">
    <h2 class="text-center mb-4">All Properties</h2>
    <div class="row g-4">
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="col-md-6 col-lg-4">
            <div class="property-card bg-white shadow-sm">
              <?php if (!empty($row['image'])): ?>
                <img src="<?= htmlspecialchars($row['image']) ?>" class="property-img" alt="Property Image">
              <?php endif; ?>
              <div class="property-info">
                <h5 class="mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                <p class="text-muted mb-2">
                  <?= htmlspecialchars($row['location']) ?> • <?= htmlspecialchars($row['property_type']) ?>
                </p>
                <p class="mb-1"><strong>Price:</strong> $<?= number_format($row['price'], 2) ?></p>
                <p class="mb-1">
                  <strong>Bedrooms:</strong> <?= htmlspecialchars($row['bedrooms']) ?>
                  | <strong>Bathrooms:</strong> <?= htmlspecialchars($row['bathrooms']) ?>
                </p>
                <p class="mb-0"><strong>Area:</strong> <?= htmlspecialchars($row['area_sqft']) ?> sqft</p>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center text-muted">No properties found.</p>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <nav class="mt-5">
      <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
          </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
          <li class="page-item">
            <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
          </li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
