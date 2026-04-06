const COOKIE_MAX_AGE_DAYS=365;const MS_PER_DAY=24*60*60*1000;function getById(id){return document.getElementById(id)}
function queryAll(selector){return Array.from(document.querySelectorAll(selector))}
function isIdEmpty(elementId){const el=getById(elementId);return el!=null?el:!1}
function buildFormBody(params,csrfToken){const pairs=Object.entries(params).filter(([,v])=>v!=null&&v!=="").map(([k,v])=>`${encodeURIComponent(k)}=${encodeURIComponent(String(v))}`);if(csrfToken!=null&&csrfToken!==""){pairs.push(`_token=${encodeURIComponent(csrfToken)}`)}
return pairs.join("&")}
function makeRequest(url,options={},behavior={reload:!0}){return fetch(url,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded"},...options,}).then((response)=>{if(behavior.reload!==!1){location.reload()}
return response})}
function getDefaultTime(){const expirationDate=new Date();expirationDate.setTime(expirationDate.getTime()+COOKIE_MAX_AGE_DAYS*MS_PER_DAY);return `expires=${expirationDate.toUTCString()}`}
function getCookie(cookieName){const name=`${cookieName}=`;const cookies=document.cookie.split(";");for(const raw of cookies){const cookie=raw.trim();if(cookie.startsWith(name)){return cookie.slice(name.length)}}
return""}
function getCsrfToken(){const meta=document.querySelector("meta[name='csrf-token']");return meta?(meta.getAttribute("content")||""):""}