# Bande-son originale du spot (synthèse), 53 s, stéréo 48 kHz
import numpy as np, wave
SR=48000; DUR=53.0; N=int(SR*DUR); t=np.arange(N)/SR
L=np.zeros(N); R=np.zeros(N)
rng=np.random.default_rng(3)
def env(start,dur,a=.02,r=.3,n=None):
    n=N; e=np.zeros(n); s=int(start*SR); d=int(dur*SR); e_seg=np.ones(d)
    at=int(a*SR); rl=int(r*SR)
    if at: e_seg[:at]=np.linspace(0,1,at)
    if rl: e_seg[-rl:]*=np.linspace(1,0,rl)
    e[s:s+d]=e_seg[:max(0,min(d,n-s))]; return e
def note(f): return 440*2**((f-69)/12)
def saw(freq,ph=0,h=10):
    return sum(np.sin(2*np.pi*freq*k*t+ph*k)/k for k in range(1,h+1))
# Nappe : progression Am - F - C - G (2 tours), attaques lentes
chords=[[57,60,64,69],[53,57,60,65],[48,55,60,64],[55,59,62,67]]
seg=DUR/8
for i in range(8):
    c=chords[i%4]; e=env(i*seg,seg+1.2,a=1.6,r=1.6)
    for j,m in enumerate(c):
        f=note(m); det=1.003
        v=(saw(f,h=6)+saw(f*det,1.3,h=6))*0.5
        L+=v*e*0.018*(1 if j%2==0 else .7); R+=v*e*0.018*(.7 if j%2==0 else 1)
# Basse grave pulsée (à partir de la scène 3)
roots=[45,41,36,43]
for i in range(8):
    if i*seg<9.4 or i*seg>48: pass
    f=note(roots[i%4]-12); e=env(i*seg,seg,a=.05,r=.4)
    sub=np.sin(2*np.pi*f*t)*e*(0.0 if i*seg+seg<9.6 else 1)
    L+=sub*.09; R+=sub*.09
# Rythme : grosse caisse et charleston, 100 BPM, de 9,6 s à 47,4 s
bpm=100; beat=60/bpm
def kick(at,g=1):
    n=int(.45*SR); s=int(at*SR); tt=np.arange(n)/SR
    f=50+120*np.exp(-tt*30); sig=np.sin(2*np.pi*np.cumsum(f)/SR)*np.exp(-tt*9)*g
    e=min(n,N-s); L[s:s+e]+=sig[:e]*.32; R[s:s+e]+=sig[:e]*.32
def hat(at,g=1):
    n=int(.08*SR); s=int(at*SR); tt=np.arange(n)/SR
    sig=rng.standard_normal(n)*np.exp(-tt*60)*g; sig=np.diff(np.concatenate([[0],sig]))
    e=min(n,N-s); L[s:s+e]+=sig[:e]*.035; R[s:s+e]+=sig[:e]*.045
x=9.6
while x<47.2:
    kick(x); 
    if x>15.2: hat(x+beat/2)
    x+=beat
# Impacts (logo, offre, fin) et souffles aux transitions
def boom(at,g=1):
    n=int(2.5*SR); s=int(at*SR); tt=np.arange(n)/SR
    f=38+60*np.exp(-tt*6); sig=(np.sin(2*np.pi*np.cumsum(f)/SR)+.3*rng.standard_normal(n)*np.exp(-tt*25))*np.exp(-tt*2.2)*g
    e=min(n,N-s); L[s:s+e]+=sig[:e]*.45; R[s:s+e]+=sig[:e]*.45
def whoosh(at,d=.7,g=1):
    n=int(d*SR); s=int(at*SR); noise=rng.standard_normal(n)
    # filtre passe-bas glissant (moyenne mobile variable)
    out=np.zeros(n); acc=0
    k=np.linspace(.02,.35,n)
    for i in range(n): acc+= (noise[i]-acc)*k[i]; out[i]=acc
    e=np.sin(np.linspace(0,np.pi,n))**2*g
    pan=np.linspace(-1,1,n); e2=min(n,N-s)
    L[s:s+e2]+=(out*e*(1-pan)/2)[:e2]*.25; R[s:s+e2]+=(out*e*(1+pan)/2)[:e2]*.25
boom(1.05,1.0)
for c in [5,9.6,15.2,20.8,26.4,31.8,37.2,42.4,47.6]: whoosh(c-.45,.75,.9)
# Montée avant l'offre puis impact sur le prix
n=int(2.2*SR); s=int(42.1*SR); tt=np.arange(n)/SR
riser=np.sin(2*np.pi*(200*tt+300*tt**2))*(tt/2.2)**2*.06
L[s:s+n]+=riser; R[s:s+n]+=riser
boom(44.3,1.1); boom(47.7,.8)
# Signature sonore de fin (accord la mineur add9)
for m in [57,64,69,71,76]:
    f=note(m); e=env(47.7,5.2,a=.01,r=4.5)
    v=np.sin(2*np.pi*f*t)*np.exp(-np.maximum(0,t-47.7)*.6)*e
    L+=v*.05; R+=v*.05
# Mixage : fondus, normalisation
fade=np.ones(N); fi=int(.4*SR); fo=int(2.2*SR)
fade[:fi]=np.linspace(0,1,fi); fade[-fo:]=np.linspace(1,0,fo)
L*=fade; R*=fade
peak=max(np.abs(L).max(),np.abs(R).max()); L=L/peak*.89; R=R/peak*.89
data=(np.stack([L,R],1)*32767).astype('<i2')
with wave.open(__import__('os').path.join(__import__('os').path.dirname(__file__),'build','music.wav'),'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes(data.tobytes())
print('ok')
