<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Layout Testing</title>
    <style>
        :root {
            --kid-bg: #FFF9E6;
            --kid-text: #5A3E36;
            --btn-green: #34D399;
            --btn-green-dark: #10B981;
            --btn-blue: #60A5FA;
            --btn-blue-dark: #3B82F6;
            --btn-red: #F87171;
            --btn-red-dark: #EF4444;
            --btn-yellow: #FCD34D;
            --btn-yellow-dark: #F59E0B;
        }

        body, html {
            background-color: var(--kid-bg);
            color: var(--kid-text);
            font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overscroll-behavior: none;
        }

        .screen-container {
            max-width: 900px;
            margin: 0 auto;
            height: 100svh; 
            display: flex;
            flex-direction: column;
            position: relative;
            background-image: radial-gradient(#FDE68A 10%, transparent 11%), radial-gradient(#FDE68A 10%, transparent 11%);
            background-size: 60px 60px;
            background-position: 0 0, 30px 30px;
        }

        /* --- HEADER --- */
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 20px;
            flex-shrink: 0;
            z-index: 10;
            position: relative;
        }
        .icon-btn {
            font-size: 24px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            box-shadow: 0 4px 0 rgba(0,0,0,0.05);
            text-decoration: none;
        }
        .progress-bar {
            flex-grow: 1;
            margin: 0 16px;
            height: 14px;
            background: white;
            border-radius: 20px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
            overflow: hidden;
            display: flex;
        }
        .progress-segment-green { width: 33%; background: var(--btn-green); }
        .progress-segment-yellow { width: 33%; background: var(--btn-yellow); }
        .progress-segment-red { width: 34%; background: var(--btn-red); }

        /* --- CONTENT WRAPPER --- */
        .content-wrapper {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding-bottom: 20px;
            overflow: hidden; 
        }

        .lion-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .action-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end; 
            flex-grow: 1;
        }

        /* --- SHARED QUESTION AREA --- */
        .lion-emoji {
            font-size: 75px;
            line-height: 1;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
            margin-bottom: 8px;
        }
        .speech-bubble {
            background: white;
            padding: 12px 24px 12px 20px; /* Slightly more padding for audio btn */
            border-radius: 20px;
            font-size: 22px;
            font-weight: 800;
            color: var(--kid-text);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            text-align: center;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .speech-bubble::before {
            content: '';
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            border-width: 0 10px 10px 10px;
            border-style: solid;
            border-color: transparent transparent white transparent;
        }
        
        .audio-btn {
            background: var(--btn-yellow);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 4px 0 var(--btn-yellow-dark);
            cursor: pointer;
            border: none;
            padding: 0;
        }
        .audio-btn:active {
            transform: translateY(4px);
            box-shadow: 0 0px 0 var(--btn-yellow-dark);
        }

        .main-image {
            max-height: 160px;
            object-fit: contain;
            display: block;
            margin-bottom: 15px;
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));
        }

        /* --- SQUARE BUTTONS --- */
        .layout-square {
            display: flex;
            justify-content: center;
            gap: 12px;
            padding: 0 10px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .square-btn {
            flex: 1;
            max-width: 100px;
            aspect-ratio: 1;
            background: white;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 6px 0 rgba(0,0,0,0.1);
            border: 3px solid transparent;
            cursor: pointer;
        }
        .square-btn img {
            max-height: 35px;
            margin-bottom: 4px;
        }
        .square-btn.green { background: var(--btn-green); color: white; border-color: var(--btn-green-dark); box-shadow: 0 6px 0 var(--btn-green-dark); }
        .square-btn.blue { background: var(--btn-blue); color: white; border-color: var(--btn-blue-dark); box-shadow: 0 6px 0 var(--btn-blue-dark); }
        .square-btn.red { background: var(--btn-red); color: white; border-color: var(--btn-red-dark); box-shadow: 0 6px 0 var(--btn-red-dark); }

        /* --- LANDSCAPE SPLIT LOGIC --- */
        @media (min-width: 500px) and (orientation: landscape) {
            .content-wrapper {
                flex-direction: row;
                align-items: stretch; 
                padding: 10px 20px 20px 20px;
                gap: 20px;
            }
            .lion-column {
                flex: 1;
                justify-content: center;
                background: rgba(255, 255, 255, 0.4);
                border-radius: 30px;
                padding: 20px;
                box-shadow: inset 0 0 20px rgba(255,255,255,0.5);
            }
            .action-column {
                flex: 1.2;
                justify-content: center; 
                background: white; 
                border-radius: 30px;
                padding: 20px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.05);
            }
            .main-image {
                max-height: 120px; 
                margin-bottom: 20px;
            }
            .lion-emoji {
                font-size: 85px;
            }
            .speech-bubble {
                margin-bottom: 0;
            }
            .layout-square {
                gap: 16px;
            }
            .square-btn {
                max-width: 110px;
            }
        }

    </style>
</head>
<body>
    <div class="screen-container">
        
        <div class="header">
            <a href="#" class="icon-btn">🏠</a>
            <div class="progress-bar">
                <div class="progress-segment-green"></div>
                <div class="progress-segment-yellow"></div>
                <div class="progress-segment-red"></div>
            </div>
            <a href="#" class="icon-btn">⚙️</a>
        </div>

        <div class="content-wrapper">
            <div class="lion-column">
                <div class="lion-emoji">🦁</div>
                <div class="speech-bubble">
                    <button class="audio-btn">🔊</button>
                    <span>What is this?</span>
                </div>
            </div>

            <div class="action-column">
                <img src="https://em-content.zobj.net/source/apple/354/red-apple_1f34e.png" alt="Apple" class="main-image" style="width: auto; margin-left: auto; margin-right: auto; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));">

                <!-- SQUARE LAYOUT (Image 1) -->
                <div class="layout-square">
                    <div class="square-btn green">
                        <div style="font-size:28px; margin-bottom:2px;">☀️</div>
                        Nature
                    </div>
                    <div class="square-btn blue">
                        <div style="font-size:28px; margin-bottom:2px;">🔤</div>
                        Letters
                    </div>
                    <div class="square-btn red">
                        <div style="font-size:28px; margin-bottom:2px;">🔢</div>
                        Numbers
                    </div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
