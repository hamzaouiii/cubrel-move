import { ref, onBeforeUnmount } from "vue";

const TRANSPARENT_PIXEL = "data:image/gif;base64,R0lGODlhAQABAAAAACw=";
let dragImage = null;

export function useDragReorder() {
  const dragIndex = ref(null);
  const dragOverZone = ref(null);
  const ghostWidth = ref(null);
  const ghostHeight = ref(null);
  const originOffset = ref({ x: 0, y: 0 });
  const dragPosition = ref({ x: 0, y: 0 });
  const ghostRenderPos = ref({ x: 0, y: 0 });

  let animationFrame = null;

  const stepGhost = () => {
    const lerp = 0.25;
    const { x: tx, y: ty } = dragPosition.value;
    const { x, y } = ghostRenderPos.value;
    ghostRenderPos.value = {
      x: x + (tx - x) * lerp,
      y: y + (ty - y) * lerp,
    };
    animationFrame = requestAnimationFrame(stepGhost);
  };

  const startAnimation = () => {
    if (animationFrame !== null) return;
    animationFrame = requestAnimationFrame(stepGhost);
  };

  const stopAnimation = () => {
    if (animationFrame !== null) {
      cancelAnimationFrame(animationFrame);
      animationFrame = null;
    }
  };

  const startDrag = (index, event) => {
    dragIndex.value = index;

    if (event.dataTransfer) {
      event.dataTransfer.effectAllowed = "move";
      try {
        if (!dragImage) {
          dragImage = new Image();
          dragImage.src = TRANSPARENT_PIXEL;
        }
        event.dataTransfer.setDragImage(dragImage, 0, 0);
      } catch (e) {}
    }

    const el = event.currentTarget;
    if (el) {
      const rect = el.getBoundingClientRect();
      ghostWidth.value = rect.width + "px";
      ghostHeight.value = rect.height + "px";
      originOffset.value = {
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
      };
      ghostRenderPos.value = { x: event.clientX, y: event.clientY };
    }

    startAnimation();
  };

  const setZoneOver = (zoneIndex, event) => {
    event.preventDefault();
    dragOverZone.value = zoneIndex;
  };

  const onGlobalDragOver = (event) => {
    if (dragIndex.value === null) return;
    dragPosition.value = { x: event.clientX, y: event.clientY };
  };

  const endDrag = () => {
    dragIndex.value = null;
    dragOverZone.value = null;
    stopAnimation();
  };

  const dropAtZone = (list, zoneIndex) => {
    if (dragIndex.value === null) return list;
    if (zoneIndex === dragIndex.value || zoneIndex === dragIndex.value + 1) {
      return list;
    }
    const next = [...list];
    const [moved] = next.splice(dragIndex.value, 1);
    const insertAt = zoneIndex > dragIndex.value ? zoneIndex - 1 : zoneIndex;
    next.splice(insertAt, 0, moved);
    return next;
  };

  onBeforeUnmount(stopAnimation);

  return {
    dragIndex,
    dragOverZone,
    ghostWidth,
    ghostHeight,
    originOffset,
    ghostRenderPos,
    startDrag,
    setZoneOver,
    onGlobalDragOver,
    endDrag,
    dropAtZone,
  };
}
