<template>
<div class="fullscreen-center">
    <div 
        class="card draggable-box"
        ref="field"
        @mousedown="dragField"
    >{{ text }}</div>

    <div ref="trailContainer" class="trail-container"></div>
</div>
</template>

<script setup>

import { ref } from 'vue';
const props = defineProps({
  text: String,
})
const field = ref(undefined);
const trailContainer = ref(undefined);
let startX = 0,
    startY = 0;

const dragField = (e) => {
    const fieldlement = field.value;

    const rect = fieldlement.getBoundingClientRect();
    startX = e.clientX - rect.left;
    startY = e.clientY - rect.top;

    const onMouseMove = (event) => {
        const x = event.clientX - startX;
        const y = event.clientY - startY;

        fieldlement.style.position = "absolute";
        fieldlement.style.left = `${x}px`;
        fieldlement.style.top = `${y}px`;

        createTrail(event.clientX, event.clientY);
    };

    const onMouseUp = () => {
        window.removeEventListener("mousemove", onMouseMove);
        window.removeEventListener("mouseup", onMouseUp);
    };

    window.addEventListener("mousemove", onMouseMove);
    window.addEventListener("mouseup", onMouseUp);
};

const createTrail = (x, y) => {
    const trail = document.createElement("div");
    trail.style.position = "fixed";
    trail.style.left = `${x}px`;
    trail.style.top = `${y}px`;
    trail.style.width = "10px";
    trail.style.height = "10px";
    trail.style.backgroundColor = "rgba(59, 130, 246, 0.7)";
    trail.style.borderRadius = "50%";
    trail.style.pointerEvents = "none";
    trail.style.transition = "opacity 0.5s ease-out, transform 0.5s ease-out";
    trail.style.cursor = "grabbing";
    


    trailContainer.value.appendChild(trail);
    requestAnimationFrame(() => {
        trail.style.opacity = "0";
        trail.style.transform = "scale(2)";
    });

    setTimeout(() => {
        trail.remove();
    }, 500);
};
</script>
