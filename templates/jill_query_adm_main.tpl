<div class="container-fluid">
<{if $now_op == 'form'}>
  <form action="<{$xoops_url}>/modules/jill_query/admin/main.php" method="post" id="myForm" class="mb-4">
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end" for="title"><{$smarty.const._MA_JILLQUERY_TITLE}></label>
      <div class="col-sm-10"><input type="text" name="title" id="title" class="form-control validate[required]" value="<{$title}>"></div>
    </div>
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end"><{$smarty.const._MA_JILLQUERY_DIRECTIONS}></label>
      <div class="col-sm-10"><{$directions_editor}></div>
    </div>
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end" for="editorEmail"><{$smarty.const._MA_JILLQUERY_EDITOREMAIL}></label>
      <div class="col-sm-10">
        <textarea name="editorEmail" id="editorEmail" class="form-control" rows="3" aria-describedby="editorEmailHelp"><{$editorEmail}></textarea>
        <div id="editorEmailHelp" class="form-text"><{$smarty.const._MA_JILLQUERY_EDITOREMAIL_INFO}></div>
      </div>
    </div>
    <fieldset class="row mb-3">
      <legend class="col-sm-2 col-form-label text-sm-end"><{$smarty.const._MA_JILLQUERY_ISENABLE}></legend>
      <div class="col-sm-10 pt-2">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="isEnable" id="isEnable_1" value="1" <{if $isEnable == 1}>checked<{/if}>>
          <label class="form-check-label" for="isEnable_1"><{$smarty.const._YES}></label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="isEnable" id="isEnable_0" value="0" <{if $isEnable == 0}>checked<{/if}>>
          <label class="form-check-label" for="isEnable_0"><{$smarty.const._NO}></label>
        </div>
      </div>
    </fieldset>
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end" for="passwd"><{$smarty.const._MA_JILLQUERY_PASSWD}></label>
      <div class="col-sm-10"><input type="text" name="passwd" id="passwd" class="form-control" value="<{$passwd}>" autocomplete="off"></div>
    </div>
    <fieldset class="row mb-3">
      <legend class="col-sm-2 col-form-label text-sm-end"><{$smarty.const._MA_JILLQUERY_ISPUBLIC_DESC}></legend>
      <div class="col-sm-10 pt-2">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="ispublic" id="ispublic_1" value="1" <{if $ispublic == 1}>checked<{/if}>>
          <label class="form-check-label" for="ispublic_1"><{$smarty.const._YES}></label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="ispublic" id="ispublic_0" value="0" <{if $ispublic == 0}>checked<{/if}>>
          <label class="form-check-label" for="ispublic_0"><{$smarty.const._MD_JILLQUERY_LOGINREQUIRED}></label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="ispublic" id="ispublic_2" value="2" <{if $ispublic == 2}>checked<{/if}>>
          <label class="form-check-label" for="ispublic_2"><{$smarty.const._MD_JILLQUERY_BINDEMAIL}></label>
        </div>
      </div>
    </fieldset>
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end" for="read_group"><{$smarty.const._MA_JILLQUERY_READ_GROUP}></label>
      <div class="col-sm-10">
        <select name="read_group[]" id="read_group" class="form-select" size="8" multiple>
          <{foreach from=$all_groups item=g}>
            <option value="<{$g.id}>" <{if $g.selected}>selected<{/if}>><{$g.name}></option>
          <{/foreach}>
        </select>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-sm-2 col-form-label text-sm-end" for="tag_sn"><{$smarty.const._MD_JILLQUERY_PREFIX_TAG}></label>
      <div class="col-sm-10">
        <select name="tag_sn" id="tag_sn" class="form-select">
          <option value="0"><{$smarty.const._MA_JILLQUERY_TAG_SET}></option>
          <{foreach from=$tags item=tag}>
            <option value="<{$tag.tag_sn}>" <{if $tag.tag_sn == $tag_sn}>selected<{/if}>><{$tag.tag}></option>
          <{/foreach}>
        </select>
      </div>
    </div>
    <{if $qsn}>
      <div class="row mb-3">
        <div class="col-sm-2 text-sm-end"><{$smarty.const._MA_JILLQUERY_COUNTER}></div>
        <div class="col-sm-10"><{$counter}></div>
      </div>
    <{/if}>
    <div class="text-center">
      <input type="hidden" name="qsn" value="<{$qsn}>">
      <input type="hidden" name="op" value="<{$next_op}>">
      <{$token_form}>
      <button type="submit" class="btn btn-primary"><{$smarty.const._TAD_SAVE}></button>
      <{if $qsn}>
        <a href="<{$xoops_url}>/modules/jill_query/admin/setcol.php?qsn=<{$qsn}>" class="btn btn-success"><{$smarty.const._MA_JILLQUERY_SETCOL}></a>
      <{/if}>
    </div>
  </form>

<{else}>
  <div class="text-end mb-2">
    <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=jill_query_create" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <{$smarty.const._MA_JILLQUERY_ADD}></a>
  </div>
  <{if $all_query}>
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle">
        <thead>
          <tr>
            <th scope="col"><{$smarty.const._MA_JILLQUERY_TITLE}>（<{$smarty.const._MA_JILLQUERY_AMOUNT}>）</th>
            <th scope="col"><{$smarty.const._MA_JILLQUERY_READ_GROUP}></th>
            <th scope="col"><{$smarty.const._MA_JILLQUERY_PASSWD}></th>
            <th scope="col"><{$smarty.const._MA_JILLQUERY_EDITOREMAIL}></th>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_ISENABLE}></th>
            <th scope="col" class="text-center"><{$smarty.const._MA_JILLQUERY_ISPUBLIC}></th>
            <th scope="col"><{$smarty.const._MA_JILLQUERY_TAG_TITLE}></th>
            <th scope="col"><{$smarty.const._TAD_FUNCTION}></th>
          </tr>
        </thead>
        <tbody>
          <{foreach from=$all_query item=q}>
            <tr>
              <td><a href="<{$xoops_url}>/modules/jill_query/index.php?qsn=<{$q.qsn}>"><{$q.title}></a>（<{if $q.rows}><{$q.rows}><{else}><{$smarty.const._MD_JILLQUERY_NODATA}><{/if}>）</td>
              <td><{$q.groups}></td>
              <td><{$q.passwd}></td>
              <td><{$q.editorEmail}></td>
              <td class="text-center"><{if $q.isEnable}><i class="fa fa-check text-success" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._YES}></span><{else}><i class="fa fa-times text-danger" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._NO}></span><{/if}></td>
              <td class="text-center">
                <{if $q.ispublic == 1}><i class="fa fa-check text-success" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._YES}></span>
                <{elseif $q.ispublic == 2}><i class="fa fa-envelope" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_JILLQUERY_BINDEMAIL}></span>
                <{else}><i class="fa fa-times text-danger" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MD_JILLQUERY_LOGINREQUIRED}></span><{/if}>
              </td>
              <td><{if $q.tag}><span class="badge" style="background-color:<{$q.tag.color}>;color:<{$q.tag.font_color}>;"><{$q.tag.tag}></span><{else}><{$smarty.const._MA_JILLQUERY_TAG_SET}><{/if}></td>
              <td class="text-nowrap">
                <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=jill_query_edit&qsn=<{$q.qsn}>" class="btn btn-sm btn-warning"><{$smarty.const._TAD_EDIT}></a>
                <a href="<{$xoops_url}>/modules/jill_query/admin/setcol.php?qsn=<{$q.qsn}>" class="btn btn-sm btn-success"><{$smarty.const._MA_JILLQUERY_SETCOL}></a>
                <a href="<{$xoops_url}>/modules/jill_query/excel.php?qsn=<{$q.qsn}>" class="btn btn-sm btn-info"><{$smarty.const._MD_JILLQUERY_DATAMANAGEMENT}></a>
                <{if $q.cols}>
                  <a href="<{$xoops_url}>/modules/jill_query/admin/main.php?op=export&qsn=<{$q.qsn}>" class="btn btn-sm btn-secondary"><{$smarty.const._MA_JILLQUERY_EXPORT}></a>
                  <form action="<{$xoops_url}>/modules/jill_query/admin/main.php" method="post" class="d-inline">
                    <input type="hidden" name="op" value="jill_query_copy">
                    <input type="hidden" name="qsn" value="<{$q.qsn}>">
                    <input type="hidden" name="XOOPS_TOKEN_REQUEST" value="<{$csrf_token}>">
                    <button type="submit" class="btn btn-sm btn-primary" title="<{$smarty.const._MA_JILLQUERY_COPY}>"><i class="fa fa-clone" aria-hidden="true"></i><span class="visually-hidden"><{$smarty.const._MA_JILLQUERY_COPY}></span></button>
                  </form>
                <{/if}>
                <button type="button" class="btn btn-sm btn-danger" onclick="jill_query_destroy_func(<{$q.qsn}>);"><{$smarty.const._TAD_DEL}></button>
              </td>
            </tr>
          <{/foreach}>
        </tbody>
      </table>
    </div>
    <{$bar}>
  <{/if}>
<{/if}>
</div>
