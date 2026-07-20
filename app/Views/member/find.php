 <main>
     <div class="page-inner-narrow">
         <h1 class="page-main-title">계정 찾기</h1>
         <p class="page-main-lead">계정을 찾기 위해 휴대폰 본인인증을 완료해주세요.</p>

         <?php if (session('error')): ?><p class="insurance-in-alert warn"><?= esc(session('error')) ?></p><?php endif; ?>

         <form class="form-box" id="account-find-form">
             <div class="form-field">
                 <label class="form-label" for="find-phone">휴대폰 번호</label>
                 <div class="combo">
                     <input class="form-input" id="find-phone" type="tel" autocomplete="tel" placeholder="본인인증 후 자동 입력됩니다." readonly />
                     <button type="button" id="btn-find-phone-auth">본인인증</button>
                 </div>
             </div>
             <div class="form-actions">
                 <button type="submit" disabled>계속</button>
             </div>
         </form>
     </div>
 </main>
<?php if (!empty($mobileOkJsUrl) && !empty($mobileOkEnabled)): ?><script src="<?= esc($mobileOkJsUrl) ?>"></script><?php endif; ?>
<script>
(() => { const form=document.getElementById('account-find-form'), phone=document.getElementById('find-phone'), auth=document.getElementById('btn-find-phone-auth'), submit=form.querySelector('[type=submit]'), enabled=<?= json_encode((bool) ($mobileOkEnabled ?? false)) ?>, requestUrl=<?= json_encode($mobileOkRequestUrl ?? '') ?>, resultUrl=<?= json_encode($mobileOkResultUrl ?? '') ?>;
window.accountFindPhoneAuthResult=(payload)=>{ if(typeof payload==='string'){try{payload=JSON.parse(payload)}catch(e){payload={}}} fetch(resultUrl,{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({payload})}).then(r=>r.json()).then(data=>{if(data.status!=='success'){alert(data.message||'본인인증에 실패했습니다.');return} phone.value=String(data.phone||'').replace(/\D/g,''); auth.textContent='인증완료'; submit.disabled=false;}).catch(()=>alert('본인인증 처리 중 오류가 발생했습니다.'));};
auth.addEventListener('click',()=>{if(!enabled||!window.MOBILEOK||!requestUrl){alert('휴대폰 본인인증 설정이 완료되지 않았습니다.');return} fetch('<?= base_url('member/find/auth-start') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(data=>{if(data.status==='success')window.MOBILEOK.process(requestUrl,'WB','accountFindPhoneAuthResult');});});
form.addEventListener('submit',e=>{e.preventDefault();fetch('<?= base_url('member/find/result') ?>',{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(data=>{if(data.status==='success')location.href=data.redirect;else alert(data.message||'계정을 찾을 수 없습니다.');});}); })();
</script>
