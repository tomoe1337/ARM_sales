<div class="modal fade" id="stopModal" tabindex="-1" aria-labelledby="stopModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="stopModal">Вы уверены?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?=$modal_message?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отменить</button>
        <a href="<?=isset($modal_action_link) ? $modal_action_link:"";?>">
          <button name = "modal_confirm" class="btn btn-primary">Подтвердить</button>
        </a>
      </div>
    </div>
  </div>
</div>