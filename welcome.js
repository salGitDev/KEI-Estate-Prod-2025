function timeSince(postTime) {
    const now = new Date();
    const seconds = Math.floor((now - postTime) / 1000);

    if (seconds < 5) return `Just now`;
    if (seconds < 60) return `${seconds} Second${seconds === 1 ? '' : 's'}`;

    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} Minute${minutes === 1 ? '' : 's'}`;

    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} Hour${hours === 1 ? '' : 's'}`;

    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} Day${days === 1 ? '' : 's'}`;

    const weeks = Math.floor(days / 7);
    if (days < 30) return `${weeks} Week${weeks === 1 ? '' : 's'}`;

    const months = Math.floor(days / 30);
    if (months < 12) return `${months} Month${months === 1 ? '' : 's'}`;

    const years = Math.floor(days / 365);
    return `${years} Year${years === 1 ? '' : 's'}`;
}

function updateTime() {
    const postTimes = document.querySelectorAll(".time-update");

    postTimes.forEach(post => {
        const timeAttr = new Date(post.getAttribute("data-time"));
        post.textContent = timeSince(timeAttr);
    });
}

updateTime();
setInterval(updateTime, 60000); // Every 1 minute
