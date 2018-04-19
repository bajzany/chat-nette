$(document).ready(function() {
  window.fbAsyncInit = function() {
    FB.init({
      appId      : '1914687711899104',
      cookie     : true,
      status     : true,
      xfbml      : true,
      version    : 'v2.12'
    });
      
    FB.AppEvents.logPageView();   
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = "https://connect.facebook.net/cs_CZ/sdk.js";
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));


    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            var uid = response.authResponse.userID;
            var accessToken = response.authResponse.accessToken;
        } else if (response.status === 'not_authorized') {
            alert("zrusena autorizace");
        } else {
            alert("neprihlaseny uzivatel");
        }
    });

    function initFBlogin() {
        FB.login(function(response) {
            if (response.authResponse) {
                FB.api('/me', function(response) {
                    console.log('Super, prihlasen' + response.name + '.');
                });
            } else {
                console.log('zrusena autorizace');
            }
        }, {scope: 'email'});
    }
})