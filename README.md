# dl3
Web content to mp3

 dl3 | web content to mp3 
 * REQUIRED unix tools in env
 * php cat tac awk youtube-dl xclip hostname tail lsof kill touch midori
 * 
 * Can run 
 * - in webserver, with folder write access
 * - from command line 
 * - from pipe
 * 
 * nk2018 @ https://github.com/webdev23

Bookmarklet (Embeded mode for youtube)

    javascript:void%20!function(){related.innerHTML=%22%3Ciframe%20style='width:100%25;height:800px'%20src='//dl3.ponyhacks.com%3Furl=%22+location.href+%22'%3E%3C/iframe%3E%22}();


Bookmarklet (New tab)

    javascript:void(window.open("//dl3.ponyhacks.com/?url="+location.href));
    
