'''

ldap.py

1/4/2024

Purpose: LDAP authentication DASH widget for WashU and BJC users

'''

#%% imports
# standard
import os

# third-party imports
from dash import html, dcc, Input, Output, State, callback
from dash.exceptions import PreventUpdate
import dash_bootstrap_components as dbc
from ldap3 import Server, Connection, SIMPLE, SYNC, ALL, Reader, NTLM


#%% module objects

# login widget object
login_widget = html.Div(
    id='login-content', 
    children = [
        dbc.Row([
            dbc.Col(
                [
                    html.Div(
                        children=[
                            dcc.Dropdown(
                                id='domain-dropdown',
                                options=[
                                    {
                                        'label':'BJC-NT',
                                        'value':'BJC-NT'
                                    },
                                    {
                                        'label':'WUSTL',
                                        'value':'WUSTL'
                                    }
                                ],
                                value='BJC-NT',
                                placeholder='Domain',
                                style={
                                    'margin-top':'10px'
                                }                    
                            ),
                            dbc.Input(
                                id='username-input',
                                type='text',
                                placeholder='Username',
                                style={
                                    'margin-top':'10px',
                                    'width':'250px'
                                },
                                n_submit=0
                            ),
                            dbc.Input(
                                id='password-input',
                                type='password',
                                placeholder='Password',
                                style={
                                    'margin-top':'10px',
                                    'width':'250px'
                                },
                                n_submit=0
                            ),
                            html.Center(
                                dbc.Button(
                                    'Login',
                                    id='login-button',
                                    n_clicks=0,
                                    size='lg',
                                    className='me-1',
                                    style={
                                        'margin-top':'10px',
                                    }
                                )
                            )                        
                        ],
                        style={
                            'margin-top':'10px',
                            'padding':'20px',
                            'border':'solid',
                            'border-radius':'10px'
                        }
                    ),
                    html.P(id='login-message',children=['Please log in to continue...']),
                ],
            ),
            dcc.Store(id='user-data',data={'is_auth':False}, storage_type='session')  
        ])
    ],
    style={
            'display': 'flex',
            'flexDirection': 'column',
            'alignItems': 'center',
            'width': '300px',  # Adjust the width as needed
            'margin': 'auto',
            'padding': '20px',
        }
)


#%% helper functions
with open('/srv/ldap.txt','r') as f:
    ldap_pass=f.read().rstrip()

# authentication callback function
@callback(
    [Output('login-message', 'children'),
     Output('user-data','data')],
    [Input('login-button', 'n_clicks'),
     Input('username-input', 'n_submit'),
     Input('password-input', 'n_submit')],
    [State('username-input', 'value'),
     State('password-input', 'value'),
     State('domain-dropdown', 'value'),
     State('user-data','data')]
)
def authenticate(n_button_clicks: int, n_enter_1: int, n_enter_2: int, username: str, password: str, selected_domain: str,data: dict):
    '''
        Authenticates user against LDAP server
    
        Parameters:
            n_clicks (int): number of times login button has been clicked
            username (str): username entered by user
            password (str): password entered by user
            selected_domain (str): domain selected by user
            data (dict): user data dictionary from data store in login widget
    '''
    if data['is_auth']:
        return(f"Logged in as {data['displayName']}. Error: user not authorized. Please contact resource administration to request access.",data)
    elif n_button_clicks + n_enter_1 + n_enter_2 == 0:
        raise PreventUpdate
    else:
        if selected_domain == 'WUSTL':
            server = Server('accounts.ad.wustl.edu',use_ssl=True,get_info=ALL)
            with Connection(server, 'CN=PATH-SVC-BindUser,OU=Service Accounts,DC=accounts,DC=ad,DC=wustl,DC=edu',ldap_pass ) as conn:
                conn.search('OU=wuit,OU=Current,OU=People,DC=accounts,DC=ad,DC=wustl,DC=edu',f'(samaccountname={username})',attributes=['*'])
                try:
                    dn = conn.response[0]['dn']
                except:
                    return("Username incorrect. Please try again...",data)
                new_data={
                    'username':username,
                    'displayName':conn.response[0]['attributes']['displayName'],
                    'is_auth':True
                }
                try:
                    with Connection(server, dn, password) as conn2:
                        return (f"Logged in as {new_data['displayName']}. Resource will display if user is authorized...",new_data)
                except:
                    return ("Password incorrect. Please try again...",data)
        elif selected_domain=='BJC-NT': 
            server = Server('bjc-nt.bjc.org',use_ssl=True,get_info=ALL,allowed_referral_hosts=[('accounts.ad.wustl.edu',True)])
            with Connection(server,user='accounts\Path-SVC-BindUser',password=ldap_pass,auto_referrals=True,authentication=NTLM) as conn:
                conn.search('DC=bjc-nt,DC=bjc,DC=org',f'(cn={username})',attributes=['*'])            
                try:
                    dn = conn.response[0]['attributes']['distinguishedName']
                except:
                    return("Username incorrect. Please try again...",data)
                new_data={
                    'username':username,
                    'displayName' : conn.response[0]['attributes']['displayName'],
                    'is_auth':True
                }
                try:
                    with Connection(server, dn, password) as conn2:
                        return (f"Logged in as {new_data['displayName']}. Resource will display if user is authorized...",new_data)
                except:
                    return ("Password incorrect. Please try again...",data)
                

# post-login callback function factory
def post_login_callback(gaurded_layout,gaurded_layout_output,user_list=None):
    '''
        Returns callback function that serves gaurded layout if user is authenticated
        
        Parameters:
            gaurded_layout (dash.html.Div): layout to be served if user is authenticated
            gaurded_layout_output (dash.Output): dash output object for indicate layout component for gaurded layout to be inserted into
            user_list (list): list of usernames of users to allow access to gaurded layout
            
        Returns:
            serve_layout (function): callback function that serves gaurded layout if user is authenticated
    '''
    @callback(
        gaurded_layout_output,
        Input('user-data','data')
    )
    def serve_layout(user_data):
        '''
            Serves gaurded layout if user is authenticated
        
            Parameters:
                user_data (dict): user data dictionary from data store in login widget
                
            Returns:
                gaurded_layout (dash.html.Div): layout to be served if user is authenticated
        '''
        if user_list is None:
            if user_data['is_auth']:
                if callable(gaurded_layout):
                    return(gaurded_layout(user_data))
                else:
                    return(gaurded_layout)
            else:
                raise PreventUpdate
        else:
            if callable(user_list):
                if user_data['is_auth'] and user_data['username'] in user_list():
                    if callable(gaurded_layout):
                        return(gaurded_layout(user_data))
                    else:
                        return(gaurded_layout)
                else:
                    raise PreventUpdate
            else:
                if user_data['is_auth'] and user_data['username'] in user_list:
                    if callable(gaurded_layout):
                        return(gaurded_layout(user_data))
                    else:
                        return(gaurded_layout)
                else:
                    raise PreventUpdate
    return(serve_layout)                
