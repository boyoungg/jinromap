/* 컨설팅 신청 관리 시스템 JS */
(function() {
    'use strict';

    // 신청 폼 유효성 검사
    window.consValidateApply = function(form) {
        var fields = [
            { name:'ca_name',         label:'이름' },
            { name:'ca_school',       label:'학교명' },
            { name:'ca_grade',        label:'학년' },
            { name:'ca_phone',        label:'연락처' },
            { name:'ca_parent_phone', label:'보호자 연락처' },
            { name:'ca_career',       label:'희망 진로' },
        ];
        for (var i = 0; i < fields.length; i++) {
            var el = form.elements[fields[i].name];
            if (el && !el.value.trim()) {
                alert(fields[i].label + '을(를) 입력해주세요.');
                el.focus();
                return false;
            }
        }
        // 전화번호 간단 검증
        var phone = form.elements['ca_phone'];
        if (phone && !/^[\d\-]{9,14}$/.test(phone.value.replace(/\s/g,''))) {
            alert('올바른 연락처를 입력해주세요. (숫자와 - 만 허용)');
            phone.focus();
            return false;
        }
        return true;
    };

    // 시간대 탭 전환
    window.consSelectTab = function(type) {
        var grids = document.querySelectorAll('.cons-type-section');
        grids.forEach(function(el) {
            el.style.display = (el.dataset.type === type) ? '' : 'none';
        });
        var tabs = document.querySelectorAll('.cons-tab');
        tabs.forEach(function(t) {
            if (t.dataset.type === type) t.classList.add('active');
            else t.classList.remove('active');
        });
        // 쿠키에 저장
        document.cookie = 'cons_type=' + type + ';path=/;max-age=86400';
    };

    // 페이지 로드 시 탭 복원
    document.addEventListener('DOMContentLoaded', function() {
        var match = document.cookie.match(/cons_type=([^;]+)/);
        var saved = match ? match[1] : 'middle';
        var firstTab = document.querySelector('.cons-tab');
        if (firstTab && document.querySelector('.cons-type-section[data-type="' + saved + '"]')) {
            consSelectTab(saved);
        } else if (firstTab) {
            consSelectTab(firstTab.dataset.type || 'middle');
        }
    });

})();
