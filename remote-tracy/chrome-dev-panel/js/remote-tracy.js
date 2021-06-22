document.addEventListener('DOMContentLoaded', () => {
	const settings = document.querySelector('body').dataset;

	const url = settings.remoteTracyServerUrl === undefined ? '' : settings.remoteTracyServerUrl;
	const bars = document.querySelector('#bars');

	let lastId = 0;

	const clear = () => {
		lastId = 0;
		bars.innerHTML = '';
	};

	const checkNewBar = () => {
		fetch(url + '/api/').then((response) => {
			if (response.ok) {
				return response.text();
			}

			return Promise.reject(response);
		}).then((data) => {
			processLastId(parseInt(data.toString(), 10));
		}).catch(() => {
			processLastId(0);
		});
	};

	const processLastId = (id) => {
		if (id < lastId) {
			clear();
		}

		if (id > lastId) {
			for (let i = lastId + 1; i <= id; i++) {
				addNewBar(i);
			}
		}
		setTimeout(() => checkNewBar(), 1000);
	};

	const addNewBar = (id) => {
		const iframe = document.createElement('iframe');
		iframe.setAttribute('src', url + '/api/?id=' + id);

		bars.prepend(iframe);

		lastId = id;
	};

	checkNewBar();

	// --- //

	document.getElementById('clear').addEventListener('click', () => {
		if (!confirm('Really?')) {
			return;
		}

		fetch(url + '/api/', {
			method: 'DELETE',
		}).then((response) => {
			if (response.ok) {
				clear();
			}
		});
	});

	// --- //

	document.addEventListener('mousemove', (event) => {
		if (event.target.tagName === 'IFRAME') {
			document.querySelectorAll('iframe').forEach((el) => {
				if (el === event.target) {
					el.classList.add('big');
				} else {
					el.classList.remove('big');
				}
			});
		}
	});
});
