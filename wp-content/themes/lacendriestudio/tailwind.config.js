module.exports = {
  content: [
    "*.{php,html,js}",
    "./assets/**/*.{html,js,jsx,ts,tsx}",
    "./**/*.php",
    "./node_modules/tw-elements/dist/js/**/*.js",
  ],
  theme: {
    fontFamily: {
      sans: ["Graphik", "sans-serif"],
      serif: ["Merriweather", "serif"],
    },
    extend: {},
  },
  plugins: [require("tw-elements/dist/plugin")],
};
