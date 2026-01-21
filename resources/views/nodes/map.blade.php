<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Карта</title>
  <style>
    :root{
      --bg: #efeccb;
      --card: rgba(255,255,255,.58);
      --text: #122012;
      --muted: rgba(18, 32, 18, .70);
      --stroke: rgba(40, 70, 40, .50);
      --shadow: 0 14px 26px rgba(0,0,0,.14);
      --shadow-soft: 0 10px 18px rgba(0,0,0,.10);
      --radius: 18px;
    }

    html, body { height: 100%; margin: 0; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; }

    #viewport{
      position: relative;
      height: 100%;
      overflow: hidden;
      background: radial-gradient(1100px 680px at 22% 18%, #fbf9e7, var(--bg));
      user-select: none;
      cursor: grab;
    }
    #viewport:active{ cursor: grabbing; }

    #world{
      position: absolute;
      left: 0; top: 0;
      transform-origin: 0 0;
      will-change: transform;
    }

    #edges{
      position: absolute;
      left: 0; top: 0;
      overflow: visible;
      pointer-events: none;
      filter: drop-shadow(0 8px 10px rgba(0,0,0,.08));
    }
    .edge{
      fill: none;
      stroke: var(--stroke);
      stroke-width: 3.2;
      stroke-linecap: round;
    }
    .edge.enter{
      stroke-dasharray: 999;
      stroke-dashoffset: 999;
      transition: stroke-dashoffset 420ms ease;
    }
    .edge.enter.done{ stroke-dashoffset: 0; }

    .node{
      position: absolute;

      width: fit-content;
      max-width: min(560px, 82vw);
      min-width: 260px;

      display: grid;
      grid-template-columns: 88px auto;
      gap: 12px;
      align-items: start;

      padding: 12px;
      border-radius: var(--radius);
      background:
        linear-gradient(180deg, rgba(255,255,255,.72), rgba(255,255,255,.22)),
        var(--card);
      border: 1px solid rgba(0,0,0,.08);
      box-shadow: var(--shadow);
      color: var(--text);

      backdrop-filter: blur(7px);
      -webkit-backdrop-filter: blur(7px);

      cursor: pointer;

      transition:
        left 420ms cubic-bezier(.2,.8,.2,1),
        top  420ms cubic-bezier(.2,.8,.2,1),
        transform 420ms cubic-bezier(.2,.8,.2,1),
        opacity 260ms ease,
        box-shadow 260ms ease,
        border-color 260ms ease;
    }
    .node:hover{
      box-shadow: 0 18px 34px rgba(0,0,0,.16);
      border-color: rgba(0,0,0,.12);
    }
    .node[aria-expanded="true"]{
      border-color: rgba(40, 70, 40, .22);
      box-shadow: 0 18px 36px rgba(0,0,0,.16);
    }

    .node .thumb{
      width: 88px;
      height: 88px;
      border-radius: 14px;
      overflow: hidden;
      background: rgba(0,0,0,.06);
      border: 1px solid rgba(0,0,0,.10);
      box-shadow: var(--shadow-soft);
      display: grid;
      place-items: center;
    }
    .node .thumb img{
      display: block;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }

    .node .content{
      min-width: 0;
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .node .titleRow{
      display: flex;
      align-items: baseline;
      gap: 10px;
      flex-wrap: wrap;
    }
    .node .label{
      font-size: 16px;
      font-weight: 750;
      letter-spacing: .2px;
      line-height: 1.2;
      white-space: normal;
      overflow-wrap: anywhere;
    }
    .node .meta{
      font-size: 12px;
      font-weight: 650;
      color: var(--muted);
      white-space: nowrap;
      opacity: .9;
    }
    .node .desc{
      font-size: 13px;
      line-height: 1.25;
      color: var(--muted);
      white-space: normal;
      overflow-wrap: anywhere;
    }

    .node.enter{
      opacity: 0;
      transform: translate(var(--from-x, 0px), var(--from-y, 0px)) scale(.96);
    }

    @media (max-width: 460px){
      .node{
        grid-template-columns: 78px auto;
        min-width: 220px;
        max-width: 88vw;
      }
      .node .thumb{ width: 78px; height: 78px; border-radius: 12px; }
      .node .label{ font-size: 15px; white-space: normal; }
    }
  </style>
</head>
<body>
  <div id="viewport">
    <div id="world">
      <svg id="edges"></svg>
      <div id="nodes"></div>
    </div>
  </div>

<script>
(() => {
  const rawNodes = @json($nodes);
  const fallbackImage = "{{ asset('default_avatar.png') }}";
  const viewport = document.getElementById('viewport');
  const world = document.getElementById('world');
  const nodesLayer = document.getElementById('nodes');
  const edgesSvg = document.getElementById('edges');

  const state = {
    nodes: new Map(),
    rootId: 'root',
    scale: 1,
    tx: 120,
    ty: 220,
    dragging: false,
    last: {x:0,y:0},
    edgeKeys: new Set(),
  };

  const X_PAD = 180;
  const Y_PAD = 34;
  const GROUP_PAD = 80;

  function makeNode({id, label, parentId=null, depth=0, img=null, desc="", expanded=false, sortOrder=0}) {
    return {
      id, label, parentId, depth,
      children: [],
      expanded,
      x: 0, y: 0,
      w: 340, h: 120,
      img,
      desc,
      sortOrder,
      el: null
    };
  }

  state.nodes.set(
    state.rootId,
    makeNode({
      id: state.rootId,
      label: '',
      depth: 0,
      expanded: true
    })
  );

  for (const item of rawNodes) {
    const id = String(item.id);
    const parentId = item.parent_id ? String(item.parent_id) : state.rootId;
    state.nodes.set(
      id,
      makeNode({
        id,
        label: item.title,
        parentId,
        depth: 0,
        img: item.image_url || null,
        desc: item.description || "",
        expanded: false,
        sortOrder: item.sort_order || 0
      })
    );
  }

  for (const node of state.nodes.values()) {
    if (node.id === state.rootId) continue;
    const parent = state.nodes.get(node.parentId) || state.nodes.get(state.rootId);
    parent.children.push(node.id);
  }

  for (const node of state.nodes.values()) {
    node.children.sort((a, b) => {
      const na = state.nodes.get(a);
      const nb = state.nodes.get(b);
      if (!na || !nb) return 0;
      if (na.sortOrder !== nb.sortOrder) return na.sortOrder - nb.sortOrder;
      return na.label.localeCompare(nb.label);
    });
  }

  function assignDepths() {
    const root = state.nodes.get(state.rootId);
    if (!root) return;
    const queue = [root];
    root.depth = 0;
    while (queue.length) {
      const current = queue.shift();
      for (const cid of current.children) {
        const child = state.nodes.get(cid);
        if (!child) continue;
        child.depth = current.depth + 1;
        queue.push(child);
      }
    }
  }
  assignDepths();

  function visibleTree() {
    const root = state.nodes.get(state.rootId);
    const visible = new Map();
    const links = [];

    function dfs(id) {
      const n = state.nodes.get(id);
      if (!n) return;
      visible.set(id, n);

      if (n.expanded) {
        for (const cid of n.children) {
          const c = state.nodes.get(cid);
          if (!c) continue;
          if (id !== state.rootId) {
            links.push({from: id, to: cid});
          }
          dfs(cid);
        }
      }
    }
    dfs(root.id);
    return {visible, links};
  }

  function ensureNodeElement(node) {
    if (node.id === state.rootId) return null;
    if (node.el) return node.el;

    const el = document.createElement('div');
    el.className = 'node';
    el.dataset.id = node.id;
    el.setAttribute('role', 'button');
    el.tabIndex = 0;

    el.innerHTML = `
      <div class="thumb" aria-hidden="true">
        <img alt="" loading="lazy" />
      </div>
      <div class="content">
        <div class="titleRow">
          <div class="label"></div>
          <div class="meta"></div>
        </div>
        <div class="desc"></div>
      </div>
    `;

    el.addEventListener('click', (e) => {
      e.stopPropagation();
      onToggle(node.id);
    });

    el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        onToggle(node.id);
      }
    });

    node.el = el;
    nodesLayer.appendChild(el);
    return el;
  }

  function fillNodeContent(node) {
    const el = ensureNodeElement(node);
    if (!el) return;
    el.querySelector('.label').textContent = node.label;
    el.querySelector('.meta').textContent = '';
    el.querySelector('.desc').textContent = node.desc;

    const img = el.querySelector('.thumb img');
    const src = node.img || fallbackImage;
    img.style.display = 'block';
    img.onerror = () => {
      img.onerror = null;
      img.src = fallbackImage;
    };
    if (img.getAttribute('src') !== src) img.setAttribute('src', src);

    el.setAttribute('aria-expanded', node.expanded ? 'true' : 'false');
    el.style.zIndex = String(1000 - node.depth);
  }

  function pruneHiddenDom(visible) {
    for (const [id, node] of state.nodes) {
      if (!node.el) continue;
      if (!visible.has(id)) {
        node.el.remove();
        node.el = null;
      }
    }
  }

  function measureVisible(visible) {
    for (const node of visible.values()) {
      if (!node.el) continue;
      node.w = node.el.offsetWidth || node.w;
      node.h = node.el.offsetHeight || node.h;
    }
  }

  function computeLayout(visible) {
    const root = state.nodes.get(state.rootId);

    function assignX(id) {
      const n = state.nodes.get(id);
      if (!n) return;
      if (n.parentId) {
        const p = state.nodes.get(n.parentId);
        if (p) n.x = p.id === state.rootId ? 0 : p.x + p.w + X_PAD;
      } else {
        n.x = 0;
      }
      if (n.expanded) for (const cid of n.children) assignX(cid);
    }
    assignX(root.id);

    function placeSubtree(id, topY) {
      const n = state.nodes.get(id);
      if (!n) return 0;

      const isLeaf = !n.expanded || n.children.length === 0;
      if (isLeaf) {
        n.y = topY;
        return n.h;
      }

      const childIds = n.children.slice();
      let cursor = topY;
      let totalChildren = 0;

      for (let i = 0; i < childIds.length; i++) {
        const cid = childIds[i];
        const c = state.nodes.get(cid);
        if (!c) continue;

        const bandH = placeSubtree(cid, cursor);
        const gap = i === childIds.length - 1
          ? 0
          : Y_PAD + ((c.expanded && c.children.length) ? GROUP_PAD : 0);
        cursor += bandH + gap;
        totalChildren += bandH + gap;
      }

      const childrenTop = topY;
      const childrenBottom = topY + totalChildren;
      const childrenMid = (childrenTop + childrenBottom) / 2;

      n.y = childrenMid - n.h / 2;

      return Math.max(n.h, totalChildren);
    }

    placeSubtree(root.id, 0);

    const nodesArr = [...visible.values()].filter(n => n.id !== state.rootId);
    if (!nodesArr.length) return;
    const centers = nodesArr.map(n => n.y + n.h / 2);
    const minC = Math.min(...centers);
    const maxC = Math.max(...centers);
    const mid = (minC + maxC) / 2;

    for (const n of nodesArr) n.y -= mid;
  }

  function updateNodePosition(node, fromXY = null) {
    const el = node.el;
    if (!el) return;

    el.style.left = `${node.x}px`;
    el.style.top  = `${node.y}px`;

    if (fromXY) {
      el.classList.add('enter');
      el.style.setProperty('--from-x', `${fromXY.x - node.x}px`);
      el.style.setProperty('--from-y', `${fromXY.y - node.y}px`);
      requestAnimationFrame(() => {
        el.classList.remove('enter');
        el.style.removeProperty('--from-x');
        el.style.removeProperty('--from-y');
        el.style.opacity = '1';
      });
    } else {
      el.style.opacity = '1';
    }
  }

  function nodeAnchorRight(node) {
    return { x: node.x + node.w, y: node.y + node.h / 2 };
  }
  function nodeAnchorLeft(node) {
    return { x: node.x, y: node.y + node.h / 2 };
  }

  function edgePath(a, b) {
    const start = nodeAnchorRight(a);
    const end   = nodeAnchorLeft(b);
    const dx = Math.max(110, (end.x - start.x) * 0.45);
    return `M ${start.x} ${start.y} C ${start.x + dx} ${start.y}, ${end.x - dx} ${end.y}, ${end.x} ${end.y}`;
  }

  function renderEdges(links, visible) {
    const nodesArr = [...visible.values()].filter(n => n.id !== state.rootId);
    if (nodesArr.length === 0) return;

    const minX = Math.min(...nodesArr.map(n => n.x)) - 260;
    const maxX = Math.max(...nodesArr.map(n => n.x + n.w)) + 260;
    const minY = Math.min(...nodesArr.map(n => n.y)) - 420;
    const maxY = Math.max(...nodesArr.map(n => n.y + n.h)) + 420;

    edgesSvg.setAttribute('width',  (maxX - minX));
    edgesSvg.setAttribute('height', (maxY - minY));
    edgesSvg.style.left = `${minX}px`;
    edgesSvg.style.top  = `${minY}px`;
    edgesSvg.setAttribute('viewBox', `${minX} ${minY} ${maxX - minX} ${maxY - minY}`);

    edgesSvg.innerHTML = '';
    for (const {from, to} of links) {
      const a = state.nodes.get(from);
      const b = state.nodes.get(to);
      if (!a?.el || !b?.el) continue;

      const key = `${from}=>${to}`;
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', edgePath(a, b));
      path.setAttribute('class', 'edge');

      if (!state.edgeKeys.has(key)) {
        state.edgeKeys.add(key);
        path.classList.add('enter');
        requestAnimationFrame(() => path.classList.add('done'));
      }

      edgesSvg.appendChild(path);
    }
  }

  function onToggle(id) {
    const node = state.nodes.get(id);
    if (!node || node.children.length === 0) return;
    node.expanded = !node.expanded;
    render();
  }

  function render() {
    const {visible, links} = visibleTree();

    const before = new Map();
    for (const [id, n] of state.nodes) {
      if (n.el) before.set(id, {x: n.x, y: n.y});
    }

    pruneHiddenDom(visible);

    for (const node of visible.values()) fillNodeContent(node);

    measureVisible(visible);
    computeLayout(visible);

    for (const [id, node] of visible) {
      if (!node.el) continue;
      const was = before.get(id);
      if (!was && node.parentId) {
        const p = state.nodes.get(node.parentId);
        const fromXY = p ? {x: p.x + 30, y: p.y + 12} : {x: node.x, y: node.y};
        updateNodePosition(node, fromXY);
      } else {
        updateNodePosition(node, null);
      }
    }

    requestAnimationFrame(() => renderEdges(links, visible));
  }

  function applyTransform() {
    world.style.transform = `translate(${state.tx}px, ${state.ty}px) scale(${state.scale})`;
  }

  viewport.addEventListener('wheel', (e) => {
    e.preventDefault();

    const rect = viewport.getBoundingClientRect();
    const mx = e.clientX - rect.left;
    const my = e.clientY - rect.top;

    const oldScale = state.scale;
    const zoom = Math.exp(-e.deltaY * 0.0012);
    const newScale = clamp(oldScale * zoom, 0.25, 2.2);

    const wx = (mx - state.tx) / oldScale;
    const wy = (my - state.ty) / oldScale;

    state.scale = newScale;
    state.tx = mx - wx * newScale;
    state.ty = my - wy * newScale;

    applyTransform();
  }, {passive:false});

  viewport.addEventListener('mousedown', (e) => {
    state.dragging = true;
    state.last.x = e.clientX;
    state.last.y = e.clientY;
  });
  window.addEventListener('mousemove', (e) => {
    if (!state.dragging) return;
    const dx = e.clientX - state.last.x;
    const dy = e.clientY - state.last.y;
    state.last.x = e.clientX;
    state.last.y = e.clientY;
    state.tx += dx;
    state.ty += dy;
    applyTransform();
  });
  window.addEventListener('mouseup', () => state.dragging = false);

  function clamp(v, a, b){ return Math.max(a, Math.min(b, v)); }

  applyTransform();
  render();
})();
</script>
</body>
</html>
