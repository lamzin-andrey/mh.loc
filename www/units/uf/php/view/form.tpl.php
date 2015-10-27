<script type="text/javasript">
    if (!window.UF) {
        window.UF = {
            objects: {}
        };
    }
    UF.objects['file-' + '<?=$this->_listen_action ?>'] = {};
</script>
<form>
    <div id="js-uf_preploader-<?=$this->_listen_action?>" class="hide">Load...</div>
    <input type="file" name="file-<?=$this->_listen_action ?>" id="file-<?=$this->_listen_action ?>" data-action="<?=$this->_listen_action ?>"/>
    <div class="red" id="js-uf_imerr-<?=$this->_listen_action?>"></div>
    <input type="hidden" name="action" id="action" value="<?=$this->_listen_action ?>">
    <input type="submit" value="<?=$this->_submit_name ?>">
</form>
