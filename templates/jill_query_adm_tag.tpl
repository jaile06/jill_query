<div class="container-fluid">
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead>
        <tr>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_DEMO}></th>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_TITLE}></th>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_FONTCOLOR}></th>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_COLOR}></th>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_ENABLE}></th>
          <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_FUNC}></th>
        </tr>
      </thead>
      <tbody>
        <{if !$edit_sn}>
          <tr class="table-info">
            <td><{$smarty.const._MA_JILLQUERY_TAG_NEW}></td>
            <td colspan="5">
              <form action="<{$xoops_url}>/modules/jill_query/admin/tag.php" method="post" class="row g-2 align-items-center">
                <div class="col-md-3"><label class="visually-hidden" for="new_tag"><{$smarty.const._MA_JILLQUERY_TAG_TITLE}></label><input type="text" name="tag" id="new_tag" class="form-control" required placeholder="<{$smarty.const._MA_JILLQUERY_TAG_TITLE}>"></div>
                <div class="col-md-2"><label class="visually-hidden" for="new_font_color"><{$smarty.const._MA_JILLQUERY_TAG_FONTCOLOR}></label><input type="text" name="font_color" id="new_font_color" class="form-control color" value="#ffffff" data-hex="true"></div>
                <div class="col-md-2"><label class="visually-hidden" for="new_color"><{$smarty.const._MA_JILLQUERY_TAG_COLOR}></label><input type="text" name="color" id="new_color" class="form-control color" value="#6c757d" data-hex="true"></div>
                <div class="col-md-3">
                  <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="enable" id="new_enable_1" value="1" checked><label class="form-check-label" for="new_enable_1"><{$smarty.const._YES}></label></div>
                  <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="enable" id="new_enable_0" value="0"><label class="form-check-label" for="new_enable_0"><{$smarty.const._NO}></label></div>
                </div>
                <div class="col-md-2">
                  <input type="hidden" name="op" value="tag_store">
                  <{$token_form}>
                  <button class="btn btn-sm btn-primary" type="submit"><{$smarty.const._TAD_SAVE}></button>
                </div>
              </form>
            </td>
          </tr>
        <{/if}>

        <{foreach from=$all_tags item=tag}>
          <{if $tag.edit}>
            <tr class="table-warning">
              <td><span class="badge" style="background-color:<{$tag.color}>;color:<{$tag.font_color}>;"><{$tag.tag}></span></td>
              <td colspan="5">
                <form action="<{$xoops_url}>/modules/jill_query/admin/tag.php" method="post" class="row g-2 align-items-center">
                  <div class="col-md-3"><label class="visually-hidden" for="tag_<{$tag.tag_sn}>"><{$smarty.const._MA_JILLQUERY_TAG_TITLE}></label><input type="text" name="tag" id="tag_<{$tag.tag_sn}>" value="<{$tag.tag}>" class="form-control" required></div>
                  <div class="col-md-2"><label class="visually-hidden" for="font_color_<{$tag.tag_sn}>"><{$smarty.const._MA_JILLQUERY_TAG_FONTCOLOR}></label><input type="text" name="font_color" id="font_color_<{$tag.tag_sn}>" class="form-control color" value="<{$tag.font_color}>" data-hex="true"></div>
                  <div class="col-md-2"><label class="visually-hidden" for="color_<{$tag.tag_sn}>"><{$smarty.const._MA_JILLQUERY_TAG_COLOR}></label><input type="text" name="color" id="color_<{$tag.tag_sn}>" class="form-control color" value="<{$tag.color}>" data-hex="true"></div>
                  <div class="col-md-3">
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="enable" id="enable_1" value="1" <{if $tag.enable}>checked<{/if}>><label class="form-check-label" for="enable_1"><{$smarty.const._YES}></label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="enable" id="enable_0" value="0" <{if !$tag.enable}>checked<{/if}>><label class="form-check-label" for="enable_0"><{$smarty.const._NO}></label></div>
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" name="tag_sn" value="<{$tag.tag_sn}>">
                    <input type="hidden" name="op" value="tag_update">
                    <{$token_form}>
                    <button class="btn btn-sm btn-primary" type="submit"><{$smarty.const._TAD_SAVE}></button>
                  </div>
                </form>
              </td>
            </tr>
          <{else}>
            <tr>
              <td><a href="<{$xoops_url}>/modules/jill_query/index.php?tag_sn=<{$tag.tag_sn}>" class="badge text-decoration-none" style="background-color:<{$tag.color}>;color:<{$tag.font_color}>;"><{$tag.tag}></a></td>
              <td><{$tag.tag}></td>
              <td><{$tag.font_color}></td>
              <td><{$tag.color}></td>
              <td><{if $tag.enable}><{$smarty.const._YES}><{else}><{$smarty.const._NO}><{/if}><{$tag.amount|string_format:$smarty.const._MA_JILLQUERY_TAG_AMOUNT}></td>
              <td class="text-nowrap">
                <a href="<{$xoops_url}>/modules/jill_query/admin/tag.php?tag_sn=<{$tag.tag_sn}>" class="btn btn-sm btn-warning"><{$smarty.const._TAD_EDIT}></a>
                <form action="<{$xoops_url}>/modules/jill_query/admin/tag.php" method="post" class="d-inline">
                  <input type="hidden" name="op" value="tag_toggle">
                  <input type="hidden" name="tag_sn" value="<{$tag.tag_sn}>">
                  <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$csrf_token}>">
                  <button type="submit" class="btn btn-sm <{if $tag.enable}>btn-secondary<{else}>btn-success<{/if}>"><{if $tag.enable}><{$smarty.const._MA_JILLQUERY_TAG_UNABLE}><{else}><{$smarty.const._MA_JILLQUERY_TAG_ABLE}><{/if}></button>
                </form>
                <{if !$tag.amount}>
                  <button type="button" class="btn btn-sm btn-danger" onclick="jill_query_tag_destroy_func(<{$tag.tag_sn}>);"><{$smarty.const._TAD_DEL}></button>
                <{/if}>
              </td>
            </tr>
          <{/if}>
        <{/foreach}>
      </tbody>
    </table>
  </div>
</div>
