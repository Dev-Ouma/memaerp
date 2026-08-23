import os
import math
import subprocess
from PIL import Image, ImageDraw, ImageFont, ImageFilter

WIDTH = 1280
HEIGHT = 720
FPS = 30
SECONDS_PER_SCENE = 4
TRANSITION_FRAMES = 15

scenes = [
    {
        "img": "/Users/wabwire/Dev/memaerp/apps/website/public/hero-campus.jpg",
        "tag": "WELCOME TO MEMA",
        "title": "MEMA UNIVERSITY CAMPUS",
        "sub": "Excellence in Research & Technology • Nairobi, Kenya",
        "zoom_in": True
    },
    {
        "img": "/Users/wabwire/Dev/memaerp/apps/website/public/campus-life.jpg",
        "tag": "STUDENT LIFE",
        "title": "INNOVATION & STUDENT COMMONS",
        "sub": "Over 5,400 Students Shaping the Digital Future",
        "zoom_in": False
    },
    {
        "img": "/Users/wabwire/Dev/memaerp/apps/website/public/ai-lab.jpg",
        "tag": "RESEARCH & DISCOVERY",
        "title": "AI & SUPERCOMPUTING LABS",
        "sub": "High-Performance Workstations, Robotics & IoT R&D",
        "zoom_in": True
    },
    {
        "img": "/Users/wabwire/Dev/memaerp/apps/website/public/library.jpg",
        "tag": "ACADEMIC EXCELLENCE",
        "title": "MODERN KNOWLEDGE CENTRE",
        "sub": "Multi-Level Library, Study Pods & Digital Archives",
        "zoom_in": False
    },
    {
        "img": "/Users/wabwire/Dev/memaerp/apps/website/public/graduation.jpg",
        "tag": "GRADUATE SUCCESS",
        "title": "EMPOWERING TOMORROW'S LEADERS",
        "sub": "96% Employment Rate • CUE Accredited Degrees",
        "zoom_in": True
    }
]

out_dir = "/tmp/mema_frames"
os.makedirs(out_dir, exist_ok=True)

# Load fonts
try:
    font_tag = ImageFont.truetype("/System/Library/Fonts/Supplemental/Arial Bold.ttf", 16)
    font_title = ImageFont.truetype("/System/Library/Fonts/Supplemental/Arial Bold.ttf", 32)
    font_sub = ImageFont.truetype("/System/Library/Fonts/Supplemental/Arial.ttf", 18)
    font_wm = ImageFont.truetype("/System/Library/Fonts/Supplemental/Arial Bold.ttf", 18)
except Exception:
    font_tag = ImageFont.load_default()
    font_title = ImageFont.load_default()
    font_sub = ImageFont.load_default()
    font_wm = ImageFont.load_default()

loaded_images = []
for s in scenes:
    im = Image.open(s["img"]).convert("RGB")
    loaded_images.append(im)

def get_scene_frame(scene_idx, progress):
    sc = scenes[scene_idx]
    base_img = loaded_images[scene_idx]
    orig_w, orig_h = base_img.size
    
    if sc["zoom_in"]:
        scale = 1.0 + progress * 0.12
    else:
        scale = 1.12 - progress * 0.12
        
    crop_w = orig_w / scale
    crop_h = orig_h / scale
    
    # Pan slightly horizontally
    pan_x = (orig_w - crop_w) * (0.3 + 0.4 * progress)
    pan_y = (orig_h - crop_h) * 0.5
    
    crop_box = (pan_x, pan_y, pan_x + crop_w, pan_y + crop_h)
    cropped = base_img.crop(crop_box).resize((WIDTH, HEIGHT), Image.Resampling.BILINEAR)
    
    # Render overlay on top
    overlay = Image.new("RGBA", (WIDTH, HEIGHT), (0, 0, 0, 0))
    draw = ImageDraw.Draw(overlay)
    
    # Top bar watermark
    draw.rectangle([(0, 0), (WIDTH, 56)], fill=(10, 62, 80, 200))
    draw.line([(0, 56), (WIDTH, 56)], fill=(230, 126, 34, 255), width=2)
    draw.text((28, 18), "🎓  MEMA UNIVERSITY  •  OFFICIAL CAMPUS TOUR", fill=(255, 255, 255, 255), font=font_wm)
    
    # Progress dots top right
    dot_start_x = WIDTH - 140
    for d in range(len(scenes)):
        color = (230, 126, 34, 255) if d == scene_idx else (255, 255, 255, 120)
        draw.ellipse([(dot_start_x + d * 22, 22), (dot_start_x + d * 22 + 12, 34)], fill=color)
    
    # Lower-third banner
    lt_h = 130
    lt_y = HEIGHT - lt_h - 36
    draw.rectangle([(36, lt_y), (WIDTH - 36, lt_y + lt_h)], fill=(7, 45, 58, 225))
    # Accent color bar on left of lower-third
    draw.rectangle([(36, lt_y), (44, lt_y + lt_h)], fill=(230, 126, 34, 255))
    # Border
    draw.rectangle([(36, lt_y), (WIDTH - 36, lt_y + lt_h)], outline=(255, 255, 255, 40), width=1)
    
    # Category tag pill
    draw.rectangle([(64, lt_y + 16), (64 + 180, lt_y + 38)], fill=(30, 132, 73, 230))
    draw.text((74, lt_y + 19), sc["tag"], fill=(255, 255, 255, 255), font=font_tag)
    
    # Title & Subtitle
    draw.text((64, lt_y + 48), sc["title"], fill=(255, 255, 255, 255), font=font_title)
    draw.text((64, lt_y + 92), sc["sub"], fill=(200, 225, 235, 255), font=font_sub)
    
    # Scene timeline bar at very bottom
    total_prog = (scene_idx + progress) / len(scenes)
    draw.rectangle([(0, HEIGHT - 6), (WIDTH * total_prog, HEIGHT)], fill=(230, 126, 34, 255))
    
    frame = Image.alpha_composite(cropped.convert("RGBA"), overlay).convert("RGB")
    return frame

total_frames = 0
frames_per_scene = FPS * SECONDS_PER_SCENE

print(f"Generating video frames ({len(scenes)} scenes x {frames_per_scene} frames)...")

for s_idx in range(len(scenes)):
    for f in range(frames_per_scene):
        progress = f / frames_per_scene
        frame = get_scene_frame(s_idx, progress)
        
        # Check crossfade to next scene
        if f >= (frames_per_scene - TRANSITION_FRAMES) and s_idx < len(scenes) - 1:
            fade_prog = (f - (frames_per_scene - TRANSITION_FRAMES)) / TRANSITION_FRAMES
            next_frame = get_scene_frame(s_idx + 1, 0.0)
            frame = Image.blend(frame, next_frame, fade_prog)
            
        frame.save(f"{out_dir}/frame_{total_frames:05d}.jpg", quality=92)
        total_frames += 1

print(f"Rendered {total_frames} frames. Encoding MP4 with audio via ffmpeg...")

mp4_path = "/Users/wabwire/Dev/memaerp/apps/website/public/campus-tour.mp4"

# Generate soundtrack and encode video with high quality H.264
cmd = [
    "ffmpeg", "-y",
    "-framerate", str(FPS),
    "-i", f"{out_dir}/frame_%05d.jpg",
    "-f", "lavfi",
    "-i", f"aevalsrc=sin(220*2*PI*t)*0.08+sin(440*2*PI*t)*0.04+sin(330*2*PI*t)*0.03:d={total_frames/FPS}",
    "-c:v", "libx264",
    "-pix_fmt", "yuv420p",
    "-crf", "20",
    "-preset", "fast",
    "-c:a", "aac",
    "-b:a", "128k",
    "-shortest",
    mp4_path
]

subprocess.run(cmd, check=True)
print(f"SUCCESS: Saved {mp4_path} ({os.path.getsize(mp4_path) / 1024 / 1024:.2f} MB)")
