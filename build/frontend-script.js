/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/api-fetch":
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
/***/ ((module) => {

module.exports = window["wp"]["apiFetch"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!****************************!*\
  !*** ./src/js/frontend.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);

function triggerConfirmationAnimation(block) {
  block.classList.remove("is-celebrating");
  void block.offsetWidth;
  block.classList.add("is-celebrating");
}
function getUserId() {
  try {
    let userId = localStorage.getItem("ud_rating_user_id");
    if (!userId) {
      userId = typeof window.crypto?.randomUUID === "function" ? window.crypto.randomUUID() : `ud-${Date.now()}-${Math.random().toString(16).slice(2)}`;
      localStorage.setItem("ud_rating_user_id", userId);
    }
    return userId;
  } catch (error) {
    return `ud-${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }
}
document.addEventListener("DOMContentLoaded", () => {
  const ratingBlocks = document.querySelectorAll(".ud-rating-block");
  if (!ratingBlocks.length) return;
  const userId = getUserId();
  document.cookie = `ud_rating_user_id=${userId}; path=/; max-age=31536000; SameSite=Lax`;
  ratingBlocks.forEach(block => {
    const launcher = block.querySelector(".ud-rating-block__launcher");
    const panel = block.querySelector(".ud-rating-block__panel");
    const closeButton = block.querySelector(".ud-rating-block__close");
    const question = block.querySelector(".ud-rating-block__question");
    const ratingThanks = block.querySelector(".ud-rating-block__rating-thanks");
    const thankyou = block.querySelector(".ud-rating-block__thankyou");
    const starsContainer = block.querySelector(".ud-rating-block__stars");
    const starButtons = Array.from(block.querySelectorAll(".ud-rating-block__star-button"));
    const commentSection = block.querySelector(".ud-rating-block__comment");
    const commentInput = block.querySelector(".ud-rating-block__comment-input");
    const commentSubmit = block.querySelector(".ud-rating-block__comment-submit");
    const googleSection = block.querySelector(".ud-rating-block__google");
    const googleLink = block.querySelector(".ud-rating-block__google-link");
    const status = block.querySelector(".ud-rating-block__status");
    if (!launcher || !panel || !starsContainer || !starButtons.length) return;
    const delay = Number.parseInt(block.dataset.delay || "0", 10);
    let currentRating = 0;
    let isSaving = false;
    starButtons.forEach((button, index) => {
      button.style.setProperty("--ud-rating-star-index", index);
    });
    window.setTimeout(() => {
      block.classList.add("is-visible");
    }, Math.max(0, delay) * 1000);
    function setPanelOpen(open) {
      panel.hidden = !open;
      launcher.setAttribute("aria-expanded", String(open));
      block.classList.toggle("is-open", open);
      document.body.classList.toggle("ud-rating-dialog-open", open);
      if (open) {
        window.requestAnimationFrame(() => {
          (currentRating ? closeButton : starButtons[0]).focus();
        });
      } else {
        launcher.focus();
      }
    }
    function updateStars(rating) {
      starButtons.forEach((button, index) => {
        const selected = index < rating;
        button.querySelector(".ud-star")?.classList.toggle("is-filled", selected);
        button.setAttribute("aria-pressed", String(currentRating > 0 && index + 1 === currentRating));
      });
      starsContainer.dataset.rated = String(rating);
    }
    async function saveRating(comment = "") {
      if (!currentRating || isSaving) return false;
      isSaving = true;
      if (commentSubmit) commentSubmit.disabled = true;
      starButtons.forEach(button => button.disabled = true);
      if (status) status.textContent = "";
      try {
        await _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
          path: "/ud-rating/v1/submit",
          method: "POST",
          data: {
            rating: currentRating,
            comment,
            user_id: userId
          }
        });
        if (block.dataset.confirmationAnimation === "1") {
          triggerConfirmationAnimation(block);
        }
        return true;
      } catch (error) {
        if (status) status.textContent = "Bitte versuche es erneut.";
        return false;
      } finally {
        isSaving = false;
        if (commentSubmit) commentSubmit.disabled = false;
        starButtons.forEach(button => button.disabled = false);
      }
    }
    function showCommentStep() {
      block.classList.add("has-rating");
      block.classList.remove("is-google-step");
      if (question) question.hidden = true;
      if (ratingThanks) {
        ratingThanks.hidden = false;
        panel.setAttribute("aria-labelledby", ratingThanks.id);
      }
      if (thankyou) thankyou.hidden = true;
      if (commentSection) commentSection.hidden = false;
      if (googleSection) googleSection.hidden = true;
      if (status) status.textContent = "";
    }
    function showGoogleStep() {
      block.classList.add("has-rating", "is-google-step");
      if (question) question.hidden = true;
      if (ratingThanks) ratingThanks.hidden = true;
      if (thankyou) {
        thankyou.hidden = false;
        panel.setAttribute("aria-labelledby", thankyou.id);
      }
      if (commentSection) commentSection.hidden = true;
      if (googleSection) googleSection.hidden = false;
      if (status) status.textContent = "";
      googleLink?.focus();
    }
    launcher.addEventListener("click", () => {
      setPanelOpen(panel.hidden);
    });
    block.addEventListener("click", event => {
      if (event.target === block && !panel.hidden) setPanelOpen(false);
    });
    closeButton?.addEventListener("click", () => setPanelOpen(false));
    starButtons.forEach((button, index) => {
      button.addEventListener("mouseenter", () => updateStars(index + 1));
      button.addEventListener("mouseleave", () => updateStars(currentRating));
      button.addEventListener("focus", () => {
        if (!currentRating) updateStars(index + 1);
      });
      button.addEventListener("blur", () => updateStars(currentRating));
      button.addEventListener("click", () => {
        currentRating = index + 1;
        updateStars(currentRating);
        showCommentStep();
        commentInput?.focus();
        void saveRating();
      });
    });
    commentSubmit?.addEventListener("click", async () => {
      const comment = commentInput?.value.trim() || "";
      if (!comment) {
        commentInput?.setAttribute("aria-invalid", "true");
        if (status) status.textContent = "Schreib uns bitte kurz deine Meinung.";
        commentInput?.focus();
        return;
      }
      commentInput?.removeAttribute("aria-invalid");
      const saved = await saveRating(comment);
      if (saved) showGoogleStep();
    });
    commentInput?.addEventListener("input", () => {
      if (commentInput.value.trim()) {
        commentInput.removeAttribute("aria-invalid");
        if (status) status.textContent = "";
      }
    });
    googleLink?.addEventListener("click", () => {
      window.setTimeout(() => setPanelOpen(false), 2000);
    });
    block.addEventListener("keydown", event => {
      if (panel.hidden) return;
      if (event.key === "Escape") {
        setPanelOpen(false);
        return;
      }
      if (event.key !== "Tab") return;
      const focusable = Array.from(panel.querySelectorAll('a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])')).filter(element => !element.hidden && element.offsetParent !== null);
      if (!focusable.length) return;
      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    });
  });
});
})();

/******/ })()
;
//# sourceMappingURL=frontend-script.js.map