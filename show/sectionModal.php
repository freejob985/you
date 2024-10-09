<?php
/**
 * sectionModal.php
 *
 * This file contains the modal dialog for changing the section of a lesson.
 * It displays a form with a dropdown select populated with all available sections.
 *
 * Dependencies:
 * - Bootstrap CSS and JS for modal styling and functionality.
 * - Functions from 'database.php' to retrieve sections.
 *
 * Note: Ensure that this file is included where needed in your main page.
 */
?>

<!-- Modal for changing section -->
<div id="sectionModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="sectionModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <!-- Modal Header with gradient background -->
      <div class="modal-header gradient-header">
        <h5 class="modal-title" id="sectionModalLabel">تغيير القسم</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- Modal Body containing the form -->
      <div class="modal-body">
        <form id="sectionForm">
          <div class="form-group">
            <label for="sectionSelect" class="form-label">اختر القسم</label>
            <select class="form-control" id="sectionSelect" required>
              <?php
              // Include the database functions
              include_once("database.php");
              // Retrieve all sections
              $sections = getAllSections();
              // Loop through sections and create options
              foreach ($sections as $section) {
                  echo '<option value="' . $section['id'] . '">' . htmlspecialchars($section['name']) . '</option>';
              }
              ?>
            </select>
          </div>
          <!-- Submit button -->
          <button type="submit" class="btn btn-primary mt-3">تحديث القسم</button>
        </form>
      </div>
    </div>
  </div>
</div>
