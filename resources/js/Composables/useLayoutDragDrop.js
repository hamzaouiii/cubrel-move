import { ref, onBeforeUnmount } from "vue";

export function useLayoutDragDrop() {
    const dragging = ref(null);
    const dragOver = ref(null);
    const originOffset = ref({ x: 0, y: 0 });
    const ghostWidth = ref(null);
    const ghostHeight = ref(null);
    const ghostRenderPos = ref({ x: 0, y: 0 });

    const dragPosition = ref({ x: 0, y: 0 });
    let ghostAnimationFrame = null;

    const transparentPixel = "data:image/gif;base64,R0lGODlhAQABAAAAACw=";
    const dragImage = new Image();
    dragImage.src = transparentPixel;

    const stepGhost = () => {
        const lerp = 0.2;
        const { x: tx, y: ty } = dragPosition.value;
        const { x, y } = ghostRenderPos.value;
        ghostRenderPos.value = {
            x: x + (tx - x) * lerp,
            y: y + (ty - y) * lerp,
        };
        ghostAnimationFrame = requestAnimationFrame(stepGhost);
    };

    const startGhostAnimation = () => {
        if (ghostAnimationFrame !== null) return;
        ghostAnimationFrame = requestAnimationFrame(stepGhost);
    };

    const stopGhostAnimation = () => {
        if (ghostAnimationFrame !== null) {
            cancelAnimationFrame(ghostAnimationFrame);
            ghostAnimationFrame = null;
        }
    };

    const beginDrag = (payload, event, itemSelector) => {
        dragging.value = payload;
        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = "move";
            event.dataTransfer.setData("text/plain", JSON.stringify(payload));
            try {
                event.dataTransfer.setDragImage(dragImage, 0, 0);
            } catch (e) {}
        }
        const el = event.target.closest(itemSelector);
        if (el) {
            ghostWidth.value = el.offsetWidth + "px";
            ghostHeight.value = el.offsetHeight + "px";
            const rect = el.getBoundingClientRect();
            originOffset.value = {
                x: event.clientX - rect.left,
                y: event.clientY - rect.top,
            };
            ghostRenderPos.value = { x: event.clientX, y: event.clientY };
        } else {
            ghostWidth.value = null;
            ghostHeight.value = null;
        }
        startGhostAnimation();
    };

    const endDrag = () => {
        dragging.value = null;
        dragOver.value = null;
        stopGhostAnimation();
    };

    const setDragOver = (payload, event) => {
        event.preventDefault();
        dragOver.value = payload;
    };

    const onGlobalDragOver = (event) => {
        if (!dragging.value) return;
        dragPosition.value = { x: event.clientX, y: event.clientY };
    };

    onBeforeUnmount(stopGhostAnimation);

    return {
        dragging,
        dragOver,
        originOffset,
        ghostWidth,
        ghostHeight,
        ghostRenderPos,
        beginDrag,
        endDrag,
        setDragOver,
        onGlobalDragOver,
        stopGhostAnimation,
    };
}
