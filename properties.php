<?php
require_once 'db.php';

$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$count_sql = "SELECT COUNT(DISTINCT p.id) FROM properties p 
              INNER JOIN property_images pi ON pi.property_id = p.id";
$count_result = $conn->query($count_sql);
$total_rows = $count_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);

$sql = "SELECT p.*, MIN(pi.image_path) AS image 
    FROM properties p 
    INNER JOIN property_images pi ON pi.property_id = p.id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC 
    LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Properties</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
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
      transition: 0.2s;
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
<body>

<div class="container pt-4">
  <div class="d-flex justify-content-start mb-4">
    <a href="index.html" class="btn border border-danger text-danger hover-bg-danger hover-text-white">
  Back to Homepage
</a>

  </div>

  <h2 class="text-center mb-4">Available Properties</h2>

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
              <p class="text-muted mb-2"><?= htmlspecialchars($row['location']) ?> • <?= htmlspecialchars($row['property_type']) ?></p>
              <p class="mb-1"><strong>Price:</strong> $<?= number_format($row['price'], 2) ?></p>
              <p class="mb-1"><strong>Bedrooms:</strong> <?= $row['bedrooms'] ?> | <strong>Bathrooms:</strong> <?= $row['bathrooms'] ?></p>
              <p class="mb-0"><strong>Area:</strong> <?= $row['area_sqft'] ?> sqft</p>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center">No properties found.</p>
    <?php endif; ?>
  </div>


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

<?php $conn->close(); ?>
