 <main>
     <div class="page-inner-narrow">
         <h1 class="page-main-title">계정 찾기</h1>
         <p class="page-main-lead">계정을 찾기 위해 전화번호를 입력해주세요.</p>

         <form class="form-box" method="post">
             <div class="form-field">
                 <label class="form-label" for="find-phone">휴대폰 번호</label>
                 <div class="combo">
                     <input class="form-input" id="find-phone" name="phone" type="tel" autocomplete="tel" placeholder="(예시) 01012345678" />
                     <button type="button" disabled>인증번호 받기</button>
                 </div>
             </div>

             <div class="form-field">
                 <label class="form-label" for="find-verify-code">인증번호</label>
                 <input
                     class="form-input"
                     id="find-verify-code"
                     name="verify_code"
                     type="text"
                     inputmode="numeric"
                     autocomplete="one-time-code"
                     placeholder="인증번호를 입력해주세요." />
             </div>
             <div class="form-actions">
                 <button type="submit" disabled>계속</button>
             </div>
         </form>
     </div>
 </main>