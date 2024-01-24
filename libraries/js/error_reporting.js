window.onload = () => {
  loadFunc(null, 0);
};

const loadFunc = (ins, idx) => {
  const code_content = document.getElementById("code_content");
  const file_path_container = document.getElementById("file_path");
  const code_error = code_list[idx];

  code_content.innerHTML = code_error.content;
  const pathSegments = code_error.file.split('/');
  const formattedPath = pathSegments.map((segment, index) => {
      return `<span class="path-segment" data-index="${index}">${index !== 0 ? ' > ' : '<i class="fas fa-folder"></i>'}${segment}</span>`;
  }).join('');

  file_path_container.innerHTML = formattedPath;

  hljs.highlightAll();

  const contentArray = code_error.content.split("\n").reduce((acc, line, index) => {
      acc[index + 1] = line;
      return acc;
  }, {});

  const lineNumberKey = Object.keys(contentArray).find(key => contentArray[key].includes(code_error.line));

  if (lineNumberKey) {
      hljs.initHighlightLinesOnLoad([
          [{ start: parseInt(lineNumberKey - 1), end: parseInt(lineNumberKey - 1), color: "rgba(255,0,0,.4)" }],
      ]);
  }

  if (ins) {
      for (let el of ins.parentElement.children) {
          if (ins.getAttribute('data-idx') == el.getAttribute('data-idx')) {
              el.classList.add("active_button");
          } else {
              el.classList.remove("active_button");
          }
      }
  }
};

var btn = $('#button_up');

$(window).scroll(function () {
  if ($(window).scrollTop() > 300) {
      btn.addClass('show');
  } else {
      btn.removeClass('show');
  }
});

btn.on('click', function (e) {
  e.preventDefault();
  $('html, body').animate({ scrollTop: 0 }, '0');
});

$.fn.isOnScreen = function () {
  var win = $(window);
  var viewport = {
      top: win.scrollTop(),
      left: win.scrollLeft()
  };
  viewport.right = viewport.left + win.width();
  viewport.bottom = viewport.top + win.height();
  var bounds = this.offset();
  bounds.right = bounds.left + this.outerWidth();
  bounds.bottom = bounds.top + this.outerHeight();
  return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));
};

$(window).scroll(function() {

  let already_visible = false

      if ($('#app_content').isOnScreen()) {
          $("#app_content_link").addClass("active_link")
          already_visible = true
          return
      } else {
          $("#app_content_link").removeClass("active_link")
      }


      if ($('#session_content').isOnScreen()) {
          $("#session_content_link").addClass("active_link")
          already_visible = true
          return
      } else {
          $("#session_content_link").removeClass("active_link")
      }

      if ($('#cookies_content').isOnScreen()) {
          console.log("visible",  $("#cookies_content_link"))
          $("#cookies_content_link").addClass("active_link")
          already_visible = true
          return
      } else {
          $("#cookies_content_link").removeClass("active_link")
      }

      if ($('#request_content').isOnScreen()) {
          $("#request_content_link").addClass("active_link")
          already_visible = true
          return
      } else {
          $("#request_content_link").removeClass("active_link")
      }

      if ($('#server_content').isOnScreen()) {
          $("#server_content_link").addClass("active_link")
          already_visible = true
          return
      } else {
          $("#server_content_link").removeClass("active_link")
      }
  
});