import { onMounted, onUnmounted } from "vue";
import axios from "axios";

const INTERVAL_MS = 5 * 60 * 1000;

export function useKeepAlive() {
  let timer = null;

  const ping = () => {
    if (document.visibilityState === "visible") {
      axios.get("/keep-alive").catch(() => {});
    }
  };

  onMounted(() => {
    timer = setInterval(ping, INTERVAL_MS);
  });

  onUnmounted(() => {
    if (timer) clearInterval(timer);
  });
}
