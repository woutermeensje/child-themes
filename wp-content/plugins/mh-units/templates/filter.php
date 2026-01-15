<?php if (!defined('ABSPATH')) exit;

$types = get_terms([
    'taxonomy' => 'mh_unit_type',
    'hide_empty' => false,
]);
?>

<div class="mh-units-filter-wrap">

  <div class="mh-filter-header">
    <h1>Doorzoek alle modulaire units</h1>
    <p>Vul hier je eigen tekst in over de units, levering, types, of wat je maar wil.</p>
  </div>

  <form class="mh-units-filter" method="get">
      <div class="mh-units-filter-row">

          <div class="mh-filter-item">
              <label for="mh_search">Zoek</label>
              <input id="mh_search" name="mh_search" type="text" placeholder="Zoek op naam, trefwoord..."
                     value="<?php echo esc_attr($search); ?>">
          </div>

          <div class="mh-filter-item">
              <label for="mh_type">Type unit</label>
              <select id="mh_type" name="mh_type">
                  <option value="">Alle types</option>
                  <?php foreach ($types as $t): ?>
                      <option value="<?php echo esc_attr($t->slug); ?>" <?php selected($type, $t->slug); ?>>
                          <?php echo esc_html($t->name); ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="mh-filter-item mh-filter-actions">
              <button type="submit" class="mh-btn">Filter</button>
              <a class="mh-btn mh-btn-ghost" href="<?php echo esc_url(get_permalink()); ?>">Reset</a>
          </div>

      </div>
  </form>

</div>

<style>
/* ✅ Container zoals Fondsen blok */
.mh-units-filter-wrap{
  max-width: 1050px;
  margin: 20px auto;
  padding: 24px;
  background: #fff;
  border: 1px solid #E0E0E0; /* grijze border */
  border-radius: 5px;
  box-shadow: 0 10px 40px -5px rgba(0,0,0,0.10);
}

/* Header */
.mh-filter-header h1{
  font-family: 'Poppins', sans-serif;
  font-size: 28px;
  line-height: 1.2;
  margin: 0 0 10px 0;
  color: #333;
  font-weight: 700;
}

.mh-filter-header p{
  font-family: 'Poppins', sans-serif;
  font-size: 15px;
  margin: 0 0 18px 0;
  color: #333;
}

/* Row layout */
.mh-units-filter-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  align-items: flex-end;
}

/* Labels */
.mh-filter-item label {
  display: block;
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  margin-bottom: 6px;
  color: #333;
}

/* Inputs/selects zoals Fondsen */
.mh-filter-item input,
.mh-filter-item select {
  width: 260px;
  padding: 12px 14px;
  font-size: 16px;
  border: 1px solid #E0E0E0;
  border-radius: 6px;
  background: #fff;
  color: #333;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
  font-family: 'Poppins', sans-serif;
}

/* Focus */
.mh-filter-item input:focus,
.mh-filter-item select:focus {
  outline: none;
  border-color: #0a6b8d;
  box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

/* Buttons */
.mh-btn {
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid #0884CC;
  background: #0884CC;
  color: #fff;
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
}

.mh-btn:hover{
  opacity: .92;
}

.mh-btn-ghost {
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid #E0E0E0;
  background: #fff;
  color: #333;
  font-family: 'Poppins', sans-serif;
  text-decoration: none;
  display: inline-block;
}

.mh-btn-ghost:hover{
  border-color: #bdbdbd;
}

/* Mobile */
@media (max-width: 600px) {
  .mh-filter-item input,
  .mh-filter-item select {
    width: 100%;
  }

  .mh-units-filter-wrap{
    padding: 16px;
  }
}
</style>
