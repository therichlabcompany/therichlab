<link rel="stylesheet" href="<?= base_url('assets/css/content.css?v=3') ?>" />
<main>
    <div class="page-inner">
        <h1 class="visually-hidden">보험설계사 목록</h1>
        <section class="fc-directory">
            <div class="directory-toolbar">
                <div class="directory-filters">
                    <div class="select-field">
                        <span class="visually-hidden">보험 종류</span>
                        <button type="button" class="directory-select" data-popup-target="#popup-insurance"
                            data-popup-sync="#fc-filter-insurance-value">
                            <span>전체</span>
                        </button>
                        <input type="hidden" id="fc-filter-insurance-value" name="insurance" value="" />
                    </div>
                    <div class="select-field">
                        <span class="visually-hidden">지역</span>
                        <div class="fc-region-inline">
                            <button type="button" class="directory-select fc-region-select"
                                data-popup-target="#popup-region" data-popup-sync="#fc-filter-region-value">
                                <span>전체</span>
                            </button>
                        </div>
                        <input type="hidden" id="fc-filter-region-value" name="region" value="" />
                    </div>
                </div>
                <div class="fc-directory-sort">
                    <button type="button" class="fc-sort-btn is-active">추천순</button>
                    <button type="button" class="fc-sort-btn">인기순</button>
                    <button type="button" class="fc-sort-btn">평점순</button>
                </div>
            </div>

            <div class="fc-profile-grid">
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">정민식</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(1,495)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>KB손해보험</span><span class="location"><span>전국</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>생명보험</span>
                                        <span>건강보험</span>
                                        <span>실손보험</span>
                                        <span>연금보험</span>
                                        <span>자녀보험</span>
                                        <span>사업자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">이서연</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(2,018)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>삼성화재</span><span class="location"><span>서울·경기</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>종신보험</span>
                                        <span>암보험</span>
                                        <span>실비보험</span>
                                        <span>운전자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">박준호</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 4.9
                                        <span class="c-rate-count">(892)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>현대해상</span><span class="location"><span>부산·울산</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>화재보험</span>
                                        <span>자동차보험</span>
                                        <span>사업자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">최유진</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(1,120)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>교보생명</span><span class="location"><span>인천·경기</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>연금</span>
                                        <span>변액</span>
                                        <span>자녀보험</span>
                                        <span>치아보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">강도윤</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 4.8
                                        <span class="c-rate-count">(634)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>메리츠화재</span><span class="location"><span>대전·세종</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>실손</span>
                                        <span>뇌심장</span>
                                        <span>치매간병</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">한지민</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(2,341)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>한화손보</span><span class="location"><span>광주·전남</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>생명보험</span>
                                        <span>건강보험</span>
                                        <span>태아보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>

                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">임하은</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(756)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>NH농협생명</span><span class="location"><span>제주</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>연금보험</span>
                                        <span>저축보험</span>
                                        <span>자녀</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">정민식</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(1,495)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>KB손해보험</span><span class="location"><span>전국</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>생명보험</span>
                                        <span>건강보험</span>
                                        <span>실손보험</span>
                                        <span>연금보험</span>
                                        <span>자녀보험</span>
                                        <span>사업자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">이서연</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(2,018)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>삼성화재</span><span class="location"><span>서울·경기</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>종신보험</span>
                                        <span>암보험</span>
                                        <span>실비보험</span>
                                        <span>운전자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">박준호</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 4.9
                                        <span class="c-rate-count">(892)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>현대해상</span><span class="location"><span>부산·울산</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>화재보험</span>
                                        <span>자동차보험</span>
                                        <span>사업자보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">최유진</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(1,120)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>교보생명</span><span class="location"><span>인천·경기</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>연금</span>
                                        <span>변액</span>
                                        <span>자녀보험</span>
                                        <span>치아보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-m.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">강도윤</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 4.8
                                        <span class="c-rate-count">(634)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>메리츠화재</span><span class="location"><span>대전·세종</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>실손</span>
                                        <span>뇌심장</span>
                                        <span>치매간병</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">한지민</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(2,341)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>한화손보</span><span class="location"><span>광주·전남</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>생명보험</span>
                                        <span>건강보험</span>
                                        <span>태아보험</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>

                <article class="card">
                    <div class="card-body">
                        <a class="card-link" href="/fc/view">
                            <div class="profile">
                                <img src="<?= SITE_IMG_URL ?>images/temp/@profile-w.png" alt="" class="avatar" />
                                <div>
                                    <p class="profile-name">임하은</p>
                                    <p class="c-rate">
                                        <span class="c-rate-star">★</span> 5.0
                                        <span class="c-rate-count">(756)</span>
                                    </p>
                                    <p class="c-dot-line">
                                        <span>NH농협생명</span><span class="location"><span>제주</span></span>
                                    </p>
                                    <div class="list-tags">
                                        <span>연금보험</span>
                                        <span>저축보험</span>
                                        <span>자녀</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </article>
            </div>
        </section>
        <nav class="c-paging">
            <ul>
                <li>
                    <a href="#" rel="prev"><span class="visually-hidden">이전 페이지</span></a>
                </li>
                <li>
                    <a href="#" aria-current="page">1</a>
                </li>
                <li>
                    <a href="#">2</a>
                </li>
                <li>
                    <a href="#">3</a>
                </li>
                <li>
                    <a href="#">4</a>
                </li>
                <li>
                    <a href="#">5</a>
                </li>
                <li>
                    <a href="#" rel="next"><span class="visually-hidden">다음 페이지</span></a>
                </li>
            </ul>
            <div>
                <a href="#">더보기</a>
            </div>
        </nav>
    </div>
</main>