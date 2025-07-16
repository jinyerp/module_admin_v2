@extends('jiny-admin::layouts.admin.main')

@section('content')
<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">관리자 메뉴 편집</h1>
    <div class="mb-6">
        <div id="tab-bar" class="flex border-b"></div>
    </div>
    <div class="bg-white rounded shadow p-6">
        <div id="tab-content">

        </div>
    </div>
    <div class="mt-8 flex justify-end">
        <form id="saveForm" method="POST" action="{{ route('admin.system.menu.update') }}">
            @csrf
            <input type="hidden" name="menu_data" id="menu_data_input">
            <x-button-primary type="submit">전체 저장</x-button-primary>
        </form>
    </div>
</div>

@include('jiny-admin::admin.menu.confirm-modal', [
    'id' => 'deleteTabModal',
    'title' => '탭 삭제 확인',
    'message' => "<span class='font-bold text-red-600' id='deleteTabName'></span> 탭을 삭제하려면<br>아래 코드를 입력하세요:"
])
@include('jiny-admin::admin.menu.confirm-modal', [
    'id' => 'deleteMenuModal',
    'title' => '메뉴 삭제 확인',
    'message' => "<span class='font-bold text-red-600' id='deleteMenuName'></span> 메뉴를 삭제하려면<br>아래 코드를 입력하세요:"
])
@include('jiny-admin::admin.menu.menu-modal')

<script>
let menuData = @json($menuData);
let tabOrder = Object.keys(menuData);
let activeTab = tabOrder[0];
let accordionState = loadAccordionState();

function getMenuPath(tab, pathArr) {
    return [tab].concat(pathArr).join('.');
}

function renderTabs() {
    const tabBar = document.getElementById('tab-bar');
    tabBar.innerHTML = '';
    tabBar.className = 'flex justify-between items-center border-b border-gray-200';

    // 왼쪽: 기존 탭들
    const leftTabs = document.createElement('div');
    leftTabs.className = 'flex';

    tabOrder.forEach((tab, idx) => {
        const btn = document.createElement('button');
        btn.className = 'px-4 py-2 -mb-px border-b-2 text-sm font-semibold focus:outline-none ' + (activeTab === tab ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500');
        btn.draggable = true;
        btn.style.background = 'none';
        btn.style.outline = 'none';
        btn.style.cursor = 'pointer';
        if (window.editingTabIdx === idx) {
            const input = document.createElement('input');
            input.type = 'text';
            input.value = tab;
            input.className = 'text-lg font-semibold border-b focus:outline-none';
            input.onkeydown = function(e) { if (e.key === 'Enter') finishEditTabKey(idx, input.value); };
            input.onblur = function() { finishEditTabKey(idx, input.value); };
            setTimeout(() => input.focus(), 0);
            btn.appendChild(input);
        } else {
            // 탭명 + 삭제 버튼
            const tabLabel = document.createElement('span');
            tabLabel.textContent = tab;
            btn.appendChild(tabLabel);
            // 삭제 버튼 (X), 탭이 2개 이상일 때만
            if (tabOrder.length > 1) {
                const delBtn = document.createElement('button');
                delBtn.type = 'button';
                delBtn.className = 'ml-1 text-gray-400 hover:text-red-500 text-xs font-bold';
                delBtn.textContent = '×';
                delBtn.onclick = (e) => {
                    e.stopPropagation();
                    showConfirmModal('deleteTabModal', tab, function() {
                        delete menuData[tab];
                        tabOrder.splice(idx, 1);
                        if (activeTab === tab) activeTab = tabOrder[0];
                        renderTabs();
                        renderTabContent();
                    });
                };
                btn.appendChild(delBtn);
            }
            btn.onclick = () => { activeTab = tab; renderTabs(); renderTabContent(); };
            btn.ondblclick = () => { window.editingTabIdx = idx; renderTabs(); };
        }
        btn.ondragstart = e => { e.dataTransfer.setData('tabIdx', idx); };
        btn.ondragover = e => { e.preventDefault(); };
        btn.ondrop = e => {
            const from = +e.dataTransfer.getData('tabIdx');
            const to = idx;
            if (from !== to) {
                const moved = tabOrder.splice(from, 1)[0];
                tabOrder.splice(to, 0, moved);
                renderTabs(); renderTabContent();
            }
        };
        leftTabs.appendChild(btn);
    });

    // 오른쪽: 새 탭 추가 버튼
    const rightBox = document.createElement('div');
    rightBox.className = 'flex items-center';
    const addTabBtn = document.createElement('button');
    addTabBtn.className = 'ml-2 px-3 py-1 rounded bg-blue-500 text-white text-sm font-semibold hover:bg-blue-600';
    addTabBtn.textContent = '+ 새 탭 추가';
    // 1. 모달 HTML을 body에 추가 (스크립트 하단에 삽입)
    document.body.insertAdjacentHTML('beforeend', `
<div id="addTabModal" class="fixed inset-0 z-50 flex items-start justify-center" style="display:none;">
  <!-- 배경 -->
  <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>
  <!-- 모달 패널 -->
  <div class="relative z-10 w-full max-w-lg mx-auto mt-[30vh] sm:rounded-lg bg-white shadow-xl transition-all p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">새 탭 추가</h3>
    <input id="addTabInput" type="text"
      class="block w-full min-w-0 grow py-1.5 pr-3 pl-1 text-base text-gray-900 placeholder:text-gray-400 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none sm:text-sm/6 mb-6"
      placeholder="새 탭 이름 입력" />
    <div class="flex justify-end gap-2">
      <button id="addTabCancel" class="inline-flex justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50">취소</button>
      <button id="addTabConfirm" class="inline-flex justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-blue-500">추가</button>
    </div>
  </div>
</div>
`);

    // 2. 모달 show/hide 함수
    function showAddTabModal() {
        document.getElementById('addTabModal').style.display = 'block';
        setTimeout(() => document.getElementById('addTabInput').focus(), 100);
    }
    function hideAddTabModal() {
        document.getElementById('addTabModal').style.display = 'none';
        document.getElementById('addTabInput').value = '';
    }

    // 3. 새 탭 추가 버튼 클릭 시 모달 show
    addTabBtn.onclick = () => {
        showAddTabModal();
    };

    // 4. 모달 내 확인/취소 버튼 이벤트
    setTimeout(() => {
        document.getElementById('addTabConfirm').onclick = function() {
            const newKey = document.getElementById('addTabInput').value.trim();
            if (newKey && !tabOrder.includes(newKey)) {
                menuData[newKey] = [];
                tabOrder.push(newKey);
                activeTab = newKey;
                renderTabs();
                renderTabContent();
                hideAddTabModal();
            }
        };
        document.getElementById('addTabCancel').onclick = hideAddTabModal;
        document.getElementById('addTabInput').onkeydown = function(e) {
            if (e.key === 'Enter') document.getElementById('addTabConfirm').click();
            if (e.key === 'Escape') hideAddTabModal();
        };
        document.getElementById('addTabModal').onclick = function(e) {
            if (e.target === this) hideAddTabModal();
        };
    }, 100);
    rightBox.appendChild(addTabBtn);

    tabBar.appendChild(leftTabs);
    tabBar.appendChild(rightBox);
}

function finishEditTabKey(idx, newKey) {
    newKey = newKey.trim();
    if (!newKey || tabOrder.includes(newKey)) {
        window.editingTabIdx = null;
        renderTabs();
        return;
    }
    const oldKey = tabOrder[idx];
    menuData[newKey] = menuData[oldKey];
    delete menuData[oldKey];
    tabOrder[idx] = newKey;
    if (activeTab === oldKey) activeTab = newKey;
    window.editingTabIdx = null;
    renderTabs();
    renderTabContent();
}

function renderTabContent() {
    const tab = activeTab;
    const container = document.getElementById('tab-content');
    container.innerHTML = '';
    const titleDiv = document.createElement('div');
    titleDiv.className = 'flex items-center justify-between mb-4';
    const title = document.createElement('h2');
    title.className = 'text-lg font-semibold cursor-pointer';
    title.textContent = tab + ' 메뉴';
    title.onclick = () => {
        const idx = tabOrder.indexOf(tab);
        window.editingTabIdx = idx;
        renderTabs();
    };
    titleDiv.appendChild(title);
    container.appendChild(titleDiv);
    // 트리 렌더링
    const tree = renderMenuTree(tab, menuData[tab], [], 0, [], []);
    container.appendChild(tree);
    // +추가 버튼(리스트 하단)
    const addBtn = document.createElement('button');
    addBtn.type = 'button';
    addBtn.className = 'btn btn-primary mt-4';
    addBtn.textContent = '+ 추가';
    addBtn.onclick = () => showMenuForm(tab);
    container.appendChild(addBtn);
}

function renderMenuTree(tab, items, pathArr, depth, parentLastArr, parentOpenArr) {
    const ul = document.createElement('ul');
    ul.className = 'space-y-0.5';
    (items || []).forEach((item, idx) => {
        const li = document.createElement('li');
        li.className = 'py-0.5';
        const row = document.createElement('div');
        row.className = 'flex justify-between items-center';
        const leftRow = document.createElement('div');
        leftRow.className = 'flex items-center gap-2';
        let treePrefix = '';
        // 트리 기호
        for (let d = 0; d < depth; d++) {
            if (parentOpenArr && parentOpenArr[d] === false) {
                treePrefix += '&nbsp;&nbsp;&nbsp;';
            } else if (parentOpenArr && parentOpenArr[d] === true) {
                treePrefix += '&nbsp;&nbsp;&nbsp;';
            } else {
                treePrefix += parentLastArr[d] ? '&nbsp;&nbsp;&nbsp;' : '│&nbsp;';
            }
        }
        const isLast = idx === items.length - 1;
        treePrefix += (depth === 0) ? '' : (isLast ? '└ ' : '├ ');
        const prefixSpan = document.createElement('span');
        prefixSpan.innerHTML = treePrefix;
        prefixSpan.style.fontFamily = 'monospace';
        prefixSpan.style.marginRight = '0.25rem';
        prefixSpan.style.marginLeft = '-1em'; // 더 왼쪽으로 이동
        leftRow.appendChild(prefixSpan);
        const menuPath = getMenuPath(tab, pathArr.concat(idx));
        let hasChildren = Array.isArray(item.children) && item.children.length > 0;
        if (hasChildren) {
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'mr-1 text-gray-500 hover:text-gray-700 focus:outline-none flex items-center';
            toggleBtn.innerHTML = accordionState[menuPath]
                ? '<svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>'
                : '<svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 6l6 6-6 6"/></svg>';
            toggleBtn.onclick = () => {
                accordionState[menuPath] = !accordionState[menuPath];
                saveAccordionState();
                renderTabContent();
            };
            leftRow.appendChild(toggleBtn);
        } else {
            const emptySpace = document.createElement('span');
            emptySpace.className = 'inline-block mr-1';
            emptySpace.style.width = '1.2em';
            leftRow.appendChild(emptySpace);
        }
        const labelSpan = document.createElement('span');
        let level = depth;
        if (item.type === 'title') {
            // 타이틀: 각 레벨별로 primary 색상(text-blue-600 등) 적용
            if (level === 0) labelSpan.className = 'font-bold text-blue-600 text-xl';
            else if (level === 1) labelSpan.className = 'font-bold text-blue-500 text-lg';
            else if (level === 2) labelSpan.className = 'font-semibold text-blue-400 text-base';
            else labelSpan.className = 'font-normal text-blue-400 text-sm';
        } else {
            // 일반 메뉴: 레벨별로 크기/굵기/색상 차등
            if (level === 0) labelSpan.className = 'font-extrabold text-black text-xl';
            else if (level === 1) labelSpan.className = 'font-bold text-gray-800 text-lg';
            else if (level === 2) labelSpan.className = 'font-semibold text-gray-700 text-base';
            else labelSpan.className = 'font-normal text-gray-600 text-sm';
        }
        labelSpan.textContent = item.label || '[이름없음]';
        leftRow.appendChild(labelSpan);
        if (item.url) {
            const urlSpan = document.createElement('span');
            urlSpan.className = 'ml-2 text-xs text-gray-400';
            urlSpan.textContent = item.url;
            leftRow.appendChild(urlSpan);
        }
        row.appendChild(leftRow);
        const right = document.createElement('div');
        right.className = 'flex items-center gap-2';
        const editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'text-blue-500';
        editBtn.textContent = '수정';
        editBtn.onclick = () => showMenuForm(tab, idx, pathArr);
        right.appendChild(editBtn);
        const delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'text-red-500';
        delBtn.textContent = '삭제';
        delBtn.onclick = () => {
            // confirm-modal로 보안키 입력 후 삭제
            showConfirmModal('deleteMenuModal', (item.label || '[이름없음]'), function() {
                let ref = items;
                ref.splice(idx, 1);
                renderTabContent();
            });
        };
        right.appendChild(delBtn);
        // 하위 메뉴가 없고, type이 'title'이 아닐 때만 버튼 표시
        if (!hasChildren && item.type !== 'title') {
            const addChildBtn = document.createElement('button');
            addChildBtn.type = 'button';
            addChildBtn.className = 'text-green-600';
            addChildBtn.textContent = '하위 메뉴 추가';
            addChildBtn.onclick = () => showMenuForm(tab, null, pathArr.concat(idx, 'children'));
            right.appendChild(addChildBtn);
        }
        row.appendChild(right);
        li.appendChild(row);
        // 하위 메뉴 트리(펼침 상태일 때만)
        if (hasChildren && accordionState[menuPath]) {
            li.appendChild(renderMenuTree(tab, item.children, pathArr.concat(idx, 'children'), depth + 1, parentLastArr.concat(isLast), (parentOpenArr || []).concat(accordionState[menuPath])));
            // 하위 메뉴 리스트의 마지막에 추가 버튼 (트리 기호 포함 들여쓰기)
            // 타이틀(type === 'title')에는 하위 메뉴 추가 버튼을 표시하지 않음
            if (item.type !== 'title') {
                let addBtnTreePrefix = '';
                for (let d = 0; d < depth + 1; d++) {
                    if (d === depth) {
                        addBtnTreePrefix += '&nbsp;&nbsp;&nbsp;';
                    } else if ((parentLastArr.concat(isLast))[d]) {
                        addBtnTreePrefix += '&nbsp;&nbsp;&nbsp;';
                    } else if ((parentOpenArr || [])[d] === false) {
                        addBtnTreePrefix += '&nbsp;&nbsp;&nbsp;';
                    } else if ((parentOpenArr || [])[d] === true) {
                        addBtnTreePrefix += '&nbsp;&nbsp;&nbsp;';
                    } else {
                        addBtnTreePrefix += '│&nbsp;';
                    }
                }
                addBtnTreePrefix += '└ ';
                const addChildBtn = document.createElement('button');
                addChildBtn.type = 'button';
                addChildBtn.className = 'text-green-600';
                addChildBtn.textContent = '하위 메뉴 추가';
                addChildBtn.onclick = () => showMenuForm(tab, null, pathArr.concat(idx, 'children'));
                const addBtnDiv = document.createElement('div');
                addBtnDiv.className = 'flex items-center';
                const addBtnPrefixSpan = document.createElement('span');
                addBtnPrefixSpan.innerHTML = addBtnTreePrefix;
                addBtnPrefixSpan.style.fontFamily = 'monospace';
                addBtnPrefixSpan.style.marginRight = '0.25rem';
                addBtnPrefixSpan.style.marginLeft = '-1em';
                addBtnDiv.appendChild(addBtnPrefixSpan);
                addBtnDiv.appendChild(addChildBtn);
                li.appendChild(addBtnDiv);
            }
        }
        ul.appendChild(li);
    });
    return ul;
}

// 메뉴 추가/수정/하위메뉴 추가 모두 showMenuModal로 처리
function showMenuForm(tab, idx = null, pathArr = []) {
    let isEdit = idx !== null;
    let options = {
        title: isEdit ? '메뉴 수정' : '메뉴 추가',
        label: isEdit ? getMenuValue(tab, idx, pathArr, 'label') : '',
        url: isEdit ? getMenuValue(tab, idx, pathArr, 'url') : '',
        icon: isEdit ? getMenuValue(tab, idx, pathArr, 'icon') : '',
        type: isEdit ? getMenuValue(tab, idx, pathArr, 'type') : 'menu'
    };
    showMenuModal(options, function(data) {
        let ref = menuData[tab];
        let parent = ref;
        let lastIdx = null;
        // parent 탐색 개선: children이 pathArr에 있으면 그 직전 idx의 children 배열을 parent로 설정
        if (pathArr.length > 0) {
            for (let i = 0; i < pathArr.length; i++) {
                if (pathArr[i] === 'children') {
                    parent = parent[lastIdx].children = parent[lastIdx].children || [];
                } else {
                    lastIdx = pathArr[i];
                }
            }
        }
        if (idx === null) {
            parent.push(data);
            // 하위 메뉴 추가 시 드롭다운 자동 펼침
            if (pathArr.length > 0) {
                const menuPath = getMenuPath(tab, pathArr);
                accordionState[menuPath] = true;
                saveAccordionState();
            }
        } else {
            parent[idx] = data;
        }
        renderTabContent();
    });
}
function getMenuValue(tab, idx, pathArr, key) {
    let ref = menuData[tab];
    let parent = ref;
    let lastIdx = null;
    if (pathArr.length > 0) {
        for (let i = 0; i < pathArr.length; i++) {
            if (pathArr[i] === 'children') {
                parent = parent[lastIdx].children = parent[lastIdx].children || [];
            } else {
                lastIdx = pathArr[i];
            }
        }
    }
    if (idx === null) return '';
    return parent[idx] && parent[idx][key] ? parent[idx][key] : '';
}
function saveAccordionState() {
    localStorage.setItem('menuAccordionState', JSON.stringify(accordionState));
}
function loadAccordionState() {
    try {
        return JSON.parse(localStorage.getItem('menuAccordionState')) || {};
    } catch { return {}; }
}
document.getElementById('saveForm').onsubmit = async function(e) {
    e.preventDefault();
    const orderedMenuData = {};
    tabOrder.forEach(key => { orderedMenuData[key] = menuData[key]; });
    document.getElementById('menu_data_input').value = JSON.stringify(orderedMenuData);
    const form = e.target;
    const formData = new FormData(form);
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value },
            body: formData
        });
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showNotification('메뉴가 성공적으로 저장되었습니다.', 'success');
            } else {
                showNotification(data.message || '저장 중 오류가 발생했습니다.', 'error');
            }
        } else {
            showNotification('저장 중 오류가 발생했습니다.', 'error');
        }
    } catch (err) {
        showNotification('저장 중 오류가 발생했습니다.', 'error');
    }
};
window.editingTabIdx = null;
renderTabs();
renderTabContent();

// 삭제 모달 show/hide 및 코드 생성, 입력값 체크, 복사 버튼 연동
let deleteTabTarget = null;
let deleteTabCode = '';
function showDeleteTabModal(tab, idx) {
    deleteTabTarget = { tab, idx };
    // 임의 코드 생성 (5자리)
    deleteTabCode = Math.random().toString(36).substring(2, 7).toUpperCase();
    document.getElementById('deleteTabName').textContent = tab;
    document.getElementById('deleteTabModalCode').textContent = deleteTabCode;
    document.getElementById('deleteTabModalInput').value = '';
    document.getElementById('deleteTabModalError').style.display = 'none';
    document.getElementById('deleteTabModal').style.display = 'flex';
    setTimeout(() => document.getElementById('deleteTabModalInput').focus(), 100);
    // 삭제 버튼 상태 초기화
    const btn = document.getElementById('deleteTabModalConfirm');
    btn.className = 'inline-flex justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 shadow-xs ring-1 ring-gray-300 ring-inset cursor-not-allowed';
    btn.disabled = true;
}
function hideDeleteTabModal() {
    document.getElementById('deleteTabModal').style.display = 'none';
    deleteTabTarget = null;
    deleteTabCode = '';
}
document.getElementById('deleteTabModalCancel').onclick = hideDeleteTabModal;
document.getElementById('deleteTabModalInput').oninput = function() {
    const val = this.value.trim().toUpperCase();
    const btn = document.getElementById('deleteTabModalConfirm');
    if (val === deleteTabCode) {
        btn.className = 'inline-flex justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500';
        btn.disabled = false;
    } else {
        btn.className = 'inline-flex justify-center rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-500 shadow-xs ring-1 ring-gray-300 ring-inset cursor-not-allowed';
        btn.disabled = true;
    }
};
document.getElementById('deleteTabModalInput').onkeydown = function(e) {
    if (e.key === 'Enter') document.getElementById('deleteTabModalConfirm').click();
    if (e.key === 'Escape') hideDeleteTabModal();
};
document.getElementById('deleteTabModal').onclick = function(e) {
    if (e.target === this) hideDeleteTabModal();
};
document.getElementById('deleteTabModalConfirm').onclick = function() {
    if (this.disabled) return;
    const val = document.getElementById('deleteTabModalInput').value.trim().toUpperCase();
    if (val === deleteTabCode) {
        // 삭제 실행
        const { tab, idx } = deleteTabTarget;
        delete menuData[tab];
        tabOrder.splice(idx, 1);
        if (activeTab === tab) activeTab = tabOrder[0];
        renderTabs();
        renderTabContent();
        hideDeleteTabModal();
    } else {
        document.getElementById('deleteTabModalError').textContent = '코드가 일치하지 않습니다.';
        document.getElementById('deleteTabModalError').style.display = 'block';
    }
};
document.getElementById('deleteTabModalCopy').onclick = function() {
    navigator.clipboard.writeText(deleteTabCode);
    this.textContent = '복사됨!';
    setTimeout(() => { this.textContent = '복사'; }, 1000);
};
</script>
@endsection
