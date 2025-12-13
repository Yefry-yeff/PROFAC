<x-app-layout>


    <div class="wrapper wrapper-content animated fadeInRight"   >

                <div class="ibox ">
                    <div class="ibox-content "  style="padding: 0">

                       
                              <iframe id="myvideo" width="100%" height="3000px" style="padding: 0" src="https://analytics.zoho.com/open-view/2278098000000153193"></iframe>

                                
                         
                           

                    </div>
                    <button onclick="openFullscreen()">Fullscreen Mode</button>
                  
                </div>


    </div>

{{-- probadnnn --}}

<script>
    /* Get the element you want displayed in fullscreen mode (a video in this example): */
    var elem = document.getElementById("myvideo");
    
    /* When the openFullscreen() function is executed, open the video in fullscreen.
    Note that we must include prefixes for different browsers, as they don't support the requestFullscreen method yet */
    function openFullscreen() {
      if (elem.requestFullscreen) {
        elem.requestFullscreen();
      } else if (elem.webkitRequestFullscreen) { /* Safari */
        elem.webkitRequestFullscreen();
      } else if (elem.msRequestFullscreen) { /* IE11 */
        elem.msRequestFullscreen();
      }
    }
    </script>
</x-app-layout>

