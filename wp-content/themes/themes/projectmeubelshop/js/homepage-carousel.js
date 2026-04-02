document.addEventListener('DOMContentLoaded', function () {
  var tracks = document.querySelectorAll('[data-carousel-track]');

  tracks.forEach(function (track) {
    var prevButton = document.querySelector('[data-carousel-prev="' + track.id + '"]');
    var nextButton = document.querySelector('[data-carousel-next="' + track.id + '"]');

    function getStep() {
      var firstCard = track.querySelector('.pms-home-carousel__item');
      if (!firstCard) return track.clientWidth * 0.9;

      var styles = window.getComputedStyle(track);
      var gap = parseFloat(styles.columnGap || styles.gap || 0);
      return firstCard.getBoundingClientRect().width + gap;
    }

    function scrollTrack(direction) {
      track.scrollBy({
        left: getStep() * direction,
        behavior: 'smooth'
      });
    }

    if (prevButton) {
      prevButton.addEventListener('click', function () {
        scrollTrack(-1);
      });
    }

    if (nextButton) {
      nextButton.addEventListener('click', function () {
        scrollTrack(1);
      });
    }
  });
});
