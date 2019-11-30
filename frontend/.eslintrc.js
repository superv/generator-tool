module.exports = {
  "env": {
      "amd": true
  },
  extends: ['plugin:vue/essential', 'eslint:recommended', 'prettier'], // extending recommended config and config derived from eslint-config-prettier
  plugins: ['prettier', 'vue'], // activating esling-plugin-prettier (--fix stuff)
  rules: {
    'prettier/prettier': [
      // customizing prettier rules (unfortunately not many of them are customizable)
      'error',
      {
        semi: false,
        singleQuote: true,
        trailingComma: 'all',
      },
    ],
    eqeqeq: ['error', 'always'], // adding some custom ESLint rules
  },
  parserOptions: {
    parser: 'babel-eslint',
    ecmaVersion: 2017,
    sourceType: 'module',
  },
  globals: {
    IS_DEV: true,
    Config: true,
    module: true,
  },
};
