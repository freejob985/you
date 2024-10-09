<?php
/**
 * tagModal.php
 *
 * This file contains the modal dialog for changing the tags of a lesson.
 * It displays a form with a text input for updating the tags.
 *
 * Dependencies:
 * - Bootstrap CSS and JS for modal styling and functionality.
 *
 * Note: Ensure that this file is included where needed in your main page.
 */
?>

<!-- Modal for changing tags -->
<div id="tagModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="tagModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <!-- Modal Header with gradient background -->
      <div class="modal-header gradient-header">
        <h5 class="modal-title" id="tagModalLabel">تغيير التصنيفات</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="إغلاق">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <!-- Modal Body containing the form -->
      <div class="modal-body">
        <form id="tagForm">
          <div class="form-group">
            <label for="tagInput" class="form-label">التصنيفات</label>
            <!-- The value is pre-filled with the current tags -->
            <input type="text" class="form-control" id="tagInput" value="<?php echo htmlspecialchars($lesson['section_tags']); ?>" required>
          </div>
          <!-- Submit button -->
          <button type="submit" class="btn btn-primary mt-3">تحديث التصنيفات</button>
        </form>
      </div>
    </div>
  </div>
</div>
