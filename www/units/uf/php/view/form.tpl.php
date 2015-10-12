<script type="text/javasript">
    if (!window.UF) {
        window.UF = {
            objects: {}
        };
    }
    UF.objects['file-' + '<?=$this->_listen_action ?>'] = {};
</script>
<form>
    <div id="js-uf_preploader-<?=$this->_listen_action?> hide">Load...</div>
    <input type="file" name="file" id="file-<?=$this->_listen_action ?>" data-action="<?=$this->_listen_action ?>"/>
    <div class="red" id="js-uf_imerr-<?=$this->_listen_action?>"></div>
</form>
