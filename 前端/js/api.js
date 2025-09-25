/* 简单通用网络请求封装（原生JS） */
(function(global){
  var BASE_URL = 'http://localhost:3344/api/';

  function safeParseJSON(text){
    try { return JSON.parse(text); } catch(e){ return null; }
  }

  function getStoredToken(){
    try {
      var t = window.localStorage.getItem('token');
      if(t){ return t; }
    } catch(e){}
    try {
      var s = window.sessionStorage.getItem('token');
      if(s){ return s; }
    } catch(e){}
    return null;
  }

  function getStoredUser(){
    var raw = null;
    try { raw = window.localStorage.getItem('user'); } catch(e){}
    if(!raw){ try { raw = window.sessionStorage.getItem('user'); } catch(e){} }
    return raw ? safeParseJSON(raw) : null;
  }

  function setAuth(token, user, remember){
    var storage;
    try {
      storage = remember ? window.localStorage : window.sessionStorage;
    } catch(e){
      storage = { setItem: function(){}, removeItem: function(){} };
    }
    try { storage.setItem('token', token || ''); } catch(e){}
    try { storage.setItem('user', JSON.stringify(user || {})); } catch(e){}
  }

  function clearAuth(){
    try { window.localStorage.removeItem('token'); } catch(e){}
    try { window.localStorage.removeItem('user'); } catch(e){}
    try { window.sessionStorage.removeItem('token'); } catch(e){}
    try { window.sessionStorage.removeItem('user'); } catch(e){}
  }

  function buildUrl(endpoint){
    if(/^https?:\/\//i.test(endpoint)){ return endpoint; }
    if(endpoint.indexOf('/') === 0){ return endpoint; }
    return BASE_URL + endpoint.replace(/^\/+/, '');
  }

  function request(method, endpoint, data, withAuth){
    var url = buildUrl(endpoint);
    var headers = { 'Content-Type': 'application/json' };
    if(withAuth){
      var token = getStoredToken();
      if(token){ headers['Authorization'] = 'Bearer ' + token; }
    }
    return fetch(url, {
      method: method,
      headers: headers,
      body: data ? JSON.stringify(data) : undefined
    }).then(function(res){
      return res.text().then(function(text){ return { ok: res.ok, status: res.status, json: safeParseJSON(text) }; });
    });
  }

  function postJson(endpoint, data, withAuth){
    return request('POST', endpoint, data, !!withAuth);
  }

  function getJson(endpoint, withAuth){
    return request('GET', endpoint, null, !!withAuth);
  }

  global.API = {
    postJson: postJson,
    getJson: getJson,
    setAuth: setAuth,
    clearAuth: clearAuth,
    getToken: getStoredToken,
    getUser: getStoredUser
  };
})(window);

