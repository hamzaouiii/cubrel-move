/**
 * OS Detection Utility for Vue Applications
 * Provides reactive and non-reactive OS detection methods
 */

// Cache the OS information to avoid repeated detection
let cachedOS = null;
let cachedIsMac = null;

/**
 * Detect the operating system
 * @returns {Object} OS information object
 */
export const detectOS = () => {
  // Return cached result if available
  if (cachedOS) return cachedOS;

  const userAgent = window.navigator.userAgent.toLowerCase();
  const platform = window.navigator.platform.toLowerCase();

  // Use modern API if available
  let platformName = "";
  if (navigator.userAgentData && navigator.userAgentData.platform) {
    platformName = navigator.userAgentData.platform.toLowerCase();
  }

  const os = {
    name: "unknown",
    isWindows: false,
    isMac: false,
    isLinux: false,
    isIOS: false,
    isAndroid: false,
    isMobile: false,
    modifierKey: "Ctrl", // Ctrl for most OS, Cmd for Mac
    modifierSymbol: "Ctrl", // Display symbol/text
  };

  // Mac detection
  if (
    platform.includes("mac") ||
    userAgent.includes("mac") ||
    platformName === "macos"
  ) {
    os.name = "macOS";
    os.isMac = true;
    os.modifierKey = "Meta";
    os.modifierSymbol = "cmd";
  }
  // iOS detection (iPhone/iPad)
  else if (
    userAgent.includes("iphone") ||
    userAgent.includes("ipad") ||
    (userAgent.includes("mac") && "ontouchend" in document)
  ) {
    os.name = "iOS";
    os.isIOS = true;
    os.isMobile = true;
    os.modifierKey = "Meta";
    os.modifierSymbol = "cmd";
  }
  // Windows detection
  else if (platform.includes("win") || userAgent.includes("windows")) {
    os.name = "Windows";
    os.isWindows = true;
    os.modifierKey = "Ctrl";
    os.modifierSymbol = "Ctrl";
  }
  // Linux detection
  else if (platform.includes("linux") || userAgent.includes("linux")) {
    os.name = "Linux";
    os.isLinux = true;
    os.modifierKey = "Ctrl";
    os.modifierSymbol = "Ctrl";
  }
  // Android detection
  else if (userAgent.includes("android")) {
    os.name = "Android";
    os.isAndroid = true;
    os.isMobile = true;
    os.modifierKey = "Ctrl";
    os.modifierSymbol = "Ctrl";
  }

  cachedOS = os;
  cachedIsMac = os.isMac;

  return os;
};

/**
 * Quick check if the OS is Mac
 * @returns {boolean}
 */
export const isMac = () => {
  if (cachedIsMac !== null) return cachedIsMac;
  return detectOS().isMac;
};

/**
 * Get the appropriate modifier key for keyboard shortcuts
 * @param {boolean} useSymbol - Whether to return symbol (⌘) or text (Cmd/Ctrl)
 * @returns {string}
 */
export const getModifierKey = (useSymbol = true) => {
  const os = detectOS();
  if (useSymbol) {
    return os.modifierSymbol;
  }
  return os.isMac ? "Cmd" : "Ctrl";
};

/**
 * Format a keyboard shortcut with the correct modifier key
 * @param {string} key - The key to combine with modifier (e.g., 'K', 'S')
 * @param {boolean} useSymbol - Whether to use symbol (⌘) or text (Cmd/Ctrl)
 * @returns {string}
 */
export const formatShortcut = (key, useSymbol = true) => {
  const modifier = getModifierKey(useSymbol);
  return `${modifier} + ${key.toUpperCase()}`;
};

/**
 * Vue Composition API composable
 * Provides reactive OS data that updates if needed (though OS doesn't change during runtime)
 * @returns {Object} Reactive OS properties
 */
export const useOS = () => {
  const os = detectOS();

  // For Vue 3 with Composition API
  return {
    osName: os.name,
    isWindows: os.isWindows,
    isMac: os.isMac,
    isLinux: os.isLinux,
    isIOS: os.isIOS,
    isAndroid: os.isAndroid,
    isMobile: os.isMobile,
    modifierKey: os.modifierKey,
    modifierSymbol: os.modifierSymbol,
    getModifierKey: (useSymbol = true) => getModifierKey(useSymbol),
    formatShortcut: (key, useSymbol = true) => formatShortcut(key, useSymbol),
  };
};
