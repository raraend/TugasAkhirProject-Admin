function scrollCarousel(direction) {
    const wrapper = document.getElementById('carouselWrapper');
    if (!wrapper) return;

    // Lebar satu slide (termasuk gap)
    const slide = wrapper.querySelector('.content-card-hover');
    if (!slide) return;

    const slideWidth = slide.offsetWidth + 16; // 16px = gap
    wrapper.scrollBy({
        left: slideWidth * direction,
        behavior: 'smooth'
    });

    // Update dot aktif
    const dots = document.querySelectorAll('.carousel-dot');
    const currentScroll = wrapper.scrollLeft + (direction > 0 ? slideWidth / 2 : -slideWidth / 2);
    const index = Math.round(currentScroll / slideWidth);
    dots.forEach(dot => dot.classList.remove('active'));
    if (dots[index]) dots[index].classList.add('active');
}
