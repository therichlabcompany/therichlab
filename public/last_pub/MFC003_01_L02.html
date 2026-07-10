(function () {
  'use strict';

  var listBody = document.getElementById('listBody');
  if (!listBody) return;

  var DEPTH_KEYS = ['d1', 'd2', 'd3', 'd4', 'd5'];

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function depthAt(page, k) {
    var key = DEPTH_KEYS[k - 1];
    var v = page[key];
    if (v && String(v).trim() !== '') return String(v).trim();

    if (page.title) {
      var parts = String(page.title).split(/\s*>\s*/);
      return parts[k - 1] ? parts[k - 1].trim() : '';
    }

    return '';
  }

  function prefixSame(prev, curr, k) {
    for (var i = 1; i <= k; i++) {
      if (depthAt(prev, i) !== depthAt(curr, i)) return false;
    }
    return true;
  }

  function depthCellsHtml(prev, curr) {
    var html = '';
    for (var k = 1; k <= 5; k++) {
      var v = depthAt(curr, k);

      if (!v) {
        html += '<td class="depth-col"></td>';
      } else if (prev && prefixSame(prev, curr, k)) {
        html += '<td class="depth-col"></td>';
      } else {
        html += '<td class="depth-col">' + esc(v) + '</td>';
      }
    }
    return html;
  }

  function getStatusClass(status) {
    if (status === '완료') return 'is-done';
    if (status === '진행중') return 'is-working';
    if (status === '보류') return 'is-hold';
    return '';
  }

  function flatten(table) {
    var result = [];
    Object.keys(table || {}).forEach(function (k) {
      (table[k] || []).forEach(function (item) {
        if (item) result.push(item);
      });
    });
    return result;
  }

  var pages = flatten(typeof TABLE_DATE !== 'undefined' ? TABLE_DATE : {});

  pages.sort(function (a, b) {
    return String(a.no).localeCompare(String(b.no), undefined, { numeric: true });
  });

  var html = '';
  var prev = null;

  pages.forEach(function (page) {
    var status = String(page.status || '').trim();
    var statusClass = getStatusClass(status);
    var path = page.path || '';

    html +=
      '<tr' +
      (statusClass ? ' class="' + statusClass + '"' : '') +
      '>' +
      '<td class="ta-center">' +
      esc(page.no) +
      '</td>' +
      '<td class="col-id">' +
      esc(page.id) +
      '</td>' +
      '<td class="ta-center col-type">' +
      esc(page.type) +
      '</td>' +
      depthCellsHtml(prev, page) +
      // 경로 (링크)
      '<td class="col-path">' +
      (path ? '<a href="' + esc(path) + '" target="_blank">' + esc(path) + '</a>' : '') +
      '</td>' +
      // 진행상태
      '<td class="col-status">' +
      (status ? '<span class="status ' + statusClass + '">' + esc(status) + '</span>' : '') +
      '</td>' +
      // 비고
      '<td class="col-note">' +
      esc(page.note) +
      '</td>' +
      '</tr>';

    prev = page;
  });

  listBody.innerHTML = html;
})();
