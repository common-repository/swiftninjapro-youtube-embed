;(function(){

  document.addEventListener('DOMContentLoaded', function(){
    fixSize();
    setInterval(function(){
      fixSize();
    }, 100);
  }, false);

  let nextLazyLoadIndex = 0;
  function fixSize(){
    let lazyLoadIndex = 0;
    let videoBorder = document.getElementsByClassName('SwiftNinjaProYoutubeIframe');
    let ratio = 16/9;
    for(i = 0; i < videoBorder.length; i++){
      let video = videoBorder[i].getElementsByTagName('iframe')[0];
      if(nextLazyLoadIndex !== false && i == nextLazyLoadIndex && video.hasAttribute('lazyloadurl')){
        let lazyLoadUrl = video.getAttribute('lazyloadurl');
        video.setAttribute('src', lazyLoadUrl);
      }
    if(videoBorder[i].hasAttribute('ratioheight')){
        videoBorder[i].style.height = (videoBorder[i].offsetWidth/ratio)+'px';
      videoBorder[i].height = (videoBorder[i].offsetWidth/ratio)+'px';
    }
      if(nextLazyLoadIndex !== false && nextLazyLoadIndex == i){lazyLoadIndex = i;}
    }
    if(nextLazyLoadIndex !== false && nextLazyLoadIndex == videoBorder.length){nextLazyLoadIndex = false;}
    else if(nextLazyLoadIndex !== false && nextLazyLoadIndex == lazyLoadIndex){nextLazyLoadIndex++;}
  }
})();
