import { useRouter } from "vue-router";

function getHeaderOffset() {
  return document.querySelector(".header")?.offsetHeight ?? 72;
}

export function useScrollTo() {
  const router = useRouter();

  function scrollToAnchor(hash) {
    const id = hash.startsWith("#") ? hash.slice(1) : hash;
    const el = document.getElementById(id);
    if (!el) return;

    const top = el.getBoundingClientRect().top + window.scrollY - getHeaderOffset();
    window.scrollTo({ top, behavior: "smooth" });
  }

  function scrollToTop(smooth = false) {
    window.scrollTo({ top: 0, behavior: smooth ? "smooth" : "instant" });
  }

  function handleAnchorClick(event, hash) {
    event.preventDefault();
    const currentPath = router.currentRoute.value.path;
    const targetPath = hash.includes("#") ? hash.split("#")[0] || currentPath : currentPath;
    const anchor = "#" + (hash.includes("#") ? hash.split("#")[1] : hash);

    if (targetPath && targetPath !== currentPath) {
      router.push(targetPath + anchor);
    } else {
      scrollToAnchor(anchor);
    }
  }

  return { scrollToAnchor, scrollToTop, handleAnchorClick };
}
